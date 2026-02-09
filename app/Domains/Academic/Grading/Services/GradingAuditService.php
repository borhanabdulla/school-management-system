<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grading\Exceptions\WeightMismatchException;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Collection;

/**
 * GradingAuditService - خدمة التدقيق الأكاديمي
 * 
 * تقوم بفحص صحة إعدادات التقييم واكتمال الرصد
 * 
 * الألوان:
 * 🔴 error: مشكلة حرجة تمنع الحساب
 * 🟡 warning: تحذير يجب مراجعته
 * 🟢 ok: كل شيء سليم
 */
class GradingAuditService
{
    // ═══════════════════════════════════════════════════════════════
    // فحص القالب
    // ═══════════════════════════════════════════════════════════════

    /**
     * فحص صحة قالب التقييم
     * 
     * @return array{status: string, issues: array}
     */
    public function auditTemplate(GradingTemplate $template): array
    {
        $issues = [];

        // 🔴 مجموع الأوزان ≠ 100%
        $totalWeight = $template->categories()
            ->whereNull('parent_id')
            ->sum('weight');

        if (abs($totalWeight - 100) > 0.01) {
            $issues[] = [
                'severity' => 'error',
                'code' => 'WEIGHT_MISMATCH',
                'message' => "مجموع الأوزان {$totalWeight}% بدلاً من 100%",
                'details' => [
                    'expected' => 100,
                    'actual' => $totalWeight,
                    'difference' => abs(100 - $totalWeight),
                ],
            ];
        }

        // 🟡 فئات بدون تقييمات (يتيمة)
        $orphanedCategories = $template->categories()
            ->whereDoesntHave('assessments')
            ->whereDoesntHave('children')
            ->whereNull('parent_id')
            ->where('mapping_type', 'manual')
            ->get();

        foreach ($orphanedCategories as $cat) {
            $issues[] = [
                'severity' => 'warning',
                'code' => 'ORPHANED_CATEGORY',
                'message' => "الفئة '{$cat->name}' لا تحتوي على تقييمات أو فئات فرعية",
                'category_id' => $cat->id,
            ];
        }

        // 🟡 فئات بـ pass_required بدون pass_threshold
        $missingThreshold = $template->categories()
            ->where('pass_required', true)
            ->whereNull('pass_threshold')
            ->get();

        foreach ($missingThreshold as $cat) {
            $issues[] = [
                'severity' => 'warning',
                'code' => 'MISSING_THRESHOLD',
                'message' => "الفئة '{$cat->name}' تتطلب نجاح لكن بدون حد أدنى",
                'category_id' => $cat->id,
            ];
        }

        return [
            'status' => $this->determineStatus($issues),
            'issues' => $issues,
            'summary' => [
                'total_categories' => $template->categories()->count(),
                'total_weight' => $totalWeight,
                'errors' => collect($issues)->where('severity', 'error')->count(),
                'warnings' => collect($issues)->where('severity', 'warning')->count(),
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // فحص اكتمال الرصد
    // ═══════════════════════════════════════════════════════════════

    /**
     * فحص اكتمال رصد الدرجات للشعبة
     * 
     * @return array{status: string, completion: float, missing: array}
     */
    public function auditClassSection(ClassSection $section, Term $term): array
    {
        $students = $section->students()->active()->get();
        $missing = [];

        foreach ($students as $student) {
            $grades = MonthlyGrade::where('student_id', $student->id)
                ->whereHas('gradebookMonth', fn($q) => $q->where('term_id', $term->id))
                ->get();

            if ($grades->isEmpty()) {
                $missing[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name_ar ?? $student->first_name_ar,
                    'status' => 'no_grades',
                ];
            }
        }

        $totalStudents = $students->count();
        $missingCount = count($missing);
        $completion = $totalStudents > 0
            ? round((($totalStudents - $missingCount) / $totalStudents) * 100, 1)
            : 0;

        return [
            'status' => match (true) {
                $missingCount === 0 => 'ok',
                $completion >= 80 => 'warning',
                default => 'error',
            },
            'completion' => $completion,
            'total_students' => $totalStudents,
            'graded_students' => $totalStudents - $missingCount,
            'missing' => $missing,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // فحص شامل
    // ═══════════════════════════════════════════════════════════════

    /**
     * فحص شامل للقالب والشعب المرتبطة
     */
    public function fullAudit(GradingTemplate $template, Collection $classSections, Term $term): array
    {
        $templateAudit = $this->auditTemplate($template);
        $sectionAudits = [];

        foreach ($classSections as $section) {
            $sectionAudits[$section->id] = $this->auditClassSection($section, $term);
        }

        $overallStatus = $templateAudit['status'];
        if ($overallStatus !== 'error') {
            foreach ($sectionAudits as $audit) {
                if ($audit['status'] === 'error') {
                    $overallStatus = 'error';
                    break;
                }
                if ($audit['status'] === 'warning') {
                    $overallStatus = 'warning';
                }
            }
        }

        return [
            'overall_status' => $overallStatus,
            'template' => $templateAudit,
            'sections' => $sectionAudits,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // Helpers
    // ═══════════════════════════════════════════════════════════════

    private function determineStatus(array $issues): string
    {
        foreach ($issues as $issue) {
            if ($issue['severity'] === 'error') {
                return 'error';
            }
        }

        return empty($issues) ? 'ok' : 'warning';
    }

    /**
     * رمي Exception إذا كان مجموع الأوزان خاطئ
     */
    public function validateTemplateOrFail(GradingTemplate $template): void
    {
        $audit = $this->auditTemplate($template);

        foreach ($audit['issues'] as $issue) {
            if ($issue['code'] === 'WEIGHT_MISMATCH') {
                throw new WeightMismatchException(
                    templateId: $template->id,
                    actualWeight: $issue['details']['actual']
                );
            }
        }
    }
}
