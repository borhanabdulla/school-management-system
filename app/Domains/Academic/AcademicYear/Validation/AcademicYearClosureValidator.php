<?php

namespace App\Domains\Academic\AcademicYear\Validation;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Services\StudentEnrollmentQueryService;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Grading\Services\GradingHealthGate;

class AcademicYearClosureValidator
{
    public function __construct(
        protected StudentEnrollmentQueryService $studentService
    ) {
    }

    /**
     * التحقق من إمكانية إغلاق السنة الدراسية
     * 
     * ✅ يعتمد على Enrollments بدلاً من current_class_section_id
     * ✅ يستخدم subquery بدلاً من pluck/whereIn
     * 
     * @return array ['can' => bool, 'issues' => array]
     */
    public function validate(AcademicYear $year): array
    {
        $issues = [];

        // 1. جميع الترمات مكتملة
        $incompleteTerms = $year->terms()
            ->where('status', '!=', TermStatus::Completed->value)
            ->count();

        if ($incompleteTerms > 0) {
            $issues[] = "توجد {$incompleteTerms} ترمات غير مكتملة";
        }

        // 1.5 إعدادات الدرجات سليمة
        $health = app(GradingHealthGate::class)->getYearIssues($year);
        if ($health['total'] > 0) {
            $issues[] = "إعدادات الدرجات غير مكتملة (Missing {$health['missing']} / Invalid {$health['invalid']})";
        }

        // 2. جميع النتائج السنوية محسوبة (لا يوجد pending)
        $pendingResults = AnnualResult::where('academic_year_id', $year->id)
            ->where('decision', 'pending')
            ->count();

        if ($pendingResults > 0) {
            $issues[] = "توجد {$pendingResults} نتيجة سنوية لم تُعالج";
        }

        // 3. جميع الطلاب المؤهلين للترحيل تم ترحيلهم
        // ✅ الآن نعتمد على Enrollments (ثابت) وليس current_section (متغير)
        $eligibleCount = $this->studentService->eligibleForClosureCount($year);

        // ✅ استخدام subquery بدلاً من pluck لتحسين الأداء
        $eligibleStudentIdsSubquery = $this->studentService->eligibleStudentIdsSubquery($year);

        $promotedCount = Promotion::where('academic_year_id', $year->id)
            ->where('is_reverted', false)
            ->whereIn('student_id', $eligibleStudentIdsSubquery)
            ->count();

        if ($promotedCount < $eligibleCount) {
            $remaining = $eligibleCount - $promotedCount;
            $issues[] = "بقي {$remaining} طالب لم يتم ترحيلهم";
        }

        return [
            'can' => empty($issues),
            'issues' => $issues,
        ];
    }
}
