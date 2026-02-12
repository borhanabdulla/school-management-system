<?php

namespace App\Domains\Academic\Promotion\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Promotion\Decision\PromotionDecisionStrategy;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Grading\Services\GradingHealthGate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Domains\Academic\Student\Services\StudentLookupService;
use App\Domains\Academic\Results\Enums\ResultDecision;

class AnnualResultService
{
    public function __construct(
        protected StudentLookupService $studentLookup,
        protected GradingHealthGate $healthGate,
        protected PromotionDecisionStrategy $decisionStrategy,
        protected GradingCalculatorService $calculator
    ) {
    }

    /**
     * Cache لإعدادات تقييم المواد لكل ترم
     */
    protected array $subjectConfigsCache = [];

    /**
     * تجميع نتائج الترمين لجميع الطلاب في سنة دراسية
     * ⚡ محسّن للأداء - استعلام واحد للنتائج بدلاً من استعلام لكل طالب
     */
    public function aggregateTermResults(AcademicYear $year): int
    {
        // 🛡️ PR-1: Guard against closed year
        app(AcademicWriteGuard::class)->assertYearNotClosed($year->id);

        $this->healthGate->assertYearHealthy($year, 'تجميع النتائج السنوية');

        $terms = $year->terms()->orderBy('order_index')->get();

        if ($terms->count() < 2) {
            throw new \App\Domains\Academic\Promotion\Exceptions\InsufficientTermsException($terms->count());
        }

        $term1 = $terms->first();
        $term2 = $terms->last();
        $termWeights = $this->resolveTermWeights($terms);
        $term1Weight = $termWeights[$term1->id] ?? 50.0;
        $term2Weight = $termWeights[$term2->id] ?? 50.0;

        // ⚡ تحميل جميع إعدادات التقييم مرة واحدة
        $this->preloadSubjectConfigs($terms);

        // ⚡ جلب جميع الطلاب المسجلين مع صفوفهم من enrollment
        // ✅ يستخدم JOIN للحصول على grade_id من enrollment (ثابت)
        // بدلاً من current_grade_id (الذي يتغير عند الترحيل)
        $students = $this->studentLookup->studentsWithEnrollmentJoin($year->id)
            ->get();

        if ($students->isEmpty()) {
            return 0;
        }

        $studentIds = $students->pluck('id')->toArray();

        // ⚡ جلب جميع النتائج للطلاب في الترمين دفعة واحدة مع eager loading
        $allResults = TermResult::whereIn('student_id', $studentIds)
            ->whereIn('term_id', [$term1->id, $term2->id])
            ->with([
                'courseOffering:id,subject_id,class_section_id',
                'courseOffering.subject:id,name',
                'courseOffering.classSection:id,grade_id',
                'failures:id,term_result_id,template_category_id,reason,required_min,actual_percentage',
            ])
            ->get();

        // ⚡ تجميع النتائج حسب الطالب
        $resultsByStudent = $allResults->groupBy('student_id');

        // ⚡ تجهيز البيانات للـ bulk insert/update
        $annualResultsData = [];
        $now = now();

        foreach ($students as $student) {
            $studentResults = $resultsByStudent->get($student->id, collect());

            if ($studentResults->isEmpty()) {
                continue;
            }

            // فصل النتائج حسب الترم
            $term1Results = $studentResults->where('term_id', $term1->id);
            $term2Results = $studentResults->where('term_id', $term2->id);

            // حساب المجاميع
            $term1Total = $term1Results->sum('total_score');
            $term1Max = $term1Results->sum('max_score');
            $term2Total = $term2Results->sum('total_score');
            $term2Max = $term2Results->sum('max_score');

            $term1WeightedTotal = $term1Total * ($term1Weight / 100);
            $term2WeightedTotal = $term2Total * ($term2Weight / 100);
            $term1WeightedMax = $term1Max * ($term1Weight / 100);
            $term2WeightedMax = $term2Max * ($term2Weight / 100);

            $annualTotal = $term1WeightedTotal + $term2WeightedTotal;
            $annualMax = $term1WeightedMax + $term2WeightedMax;
            $percentage = $annualMax > 0 ? ($annualTotal / $annualMax) * 100 : 0;

            // تحديد المواد الراسب فيها
            $failedSubjects = $this->calculateFailedSubjectsOptimized(
                $term1Results,
                $term2Results,
                $term1,
                $term2,
                $termWeights
            );

            $annualResultsData[] = [
                'student_id' => $student->id,
                'academic_year_id' => $year->id,
                // ✅ استخدام grade_id من enrollment (ثابت)
                // بدلاً من current_grade_id (يتغير عند الترحيل)
                'grade_id' => $student->enrollment_grade_id,
                'term1_total' => $term1Total,
                'term1_max' => $term1Max,
                'term2_total' => $term2Total,
                'term2_max' => $term2Max,
                'annual_total' => $annualTotal,
                'annual_max' => $annualMax,
                'percentage' => $percentage,
                'failed_subjects' => json_encode($failedSubjects),
                'failed_count' => count($failedSubjects),
                'grade_label' => $this->calculator->resolveGradeLabel($percentage),
                'decision' => ResultDecision::Pending,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($annualResultsData)) {
            return 0;
        }

        // ⚡ Bulk upsert بدلاً من updateOrCreate لكل طالب
        DB::transaction(function () use ($annualResultsData) {
            foreach (array_chunk($annualResultsData, 100) as $chunk) {
                AnnualResult::upsert(
                    $chunk,
                    ['student_id', 'academic_year_id'],
                    ['grade_id', 'term1_total', 'term1_max', 'term2_total', 'term2_max', 'annual_total', 'annual_max', 'percentage', 'failed_subjects', 'failed_count', 'grade_label', 'decision', 'updated_at']
                );
            }
        });

        return count($annualResultsData);
    }

    /**
     * تحميل جميع إعدادات التقييم مسبقاً
     */
    protected function preloadSubjectConfigs(Collection $terms): void
    {
        $this->subjectConfigsCache = SubjectGradingConfig::whereIn('term_id', $terms->pluck('id'))
            ->get()
            ->keyBy(fn($config) => "{$config->grade_id}_{$config->subject_id}_{$config->term_id}")
            ->toArray();
    }

    /**
     * جلب إعداد تقييم المادة من الـ cache
     */
    protected function getSubjectConfig(int $gradeId, int $subjectId, int $termId): ?object
    {
        $config = $this->subjectConfigsCache["{$gradeId}_{$subjectId}_{$termId}"] ?? null;
        return $config ? (object) $config : null;
    }

    /**
     * تحديد المواد الراسب فيها سنوياً (محسّن)
     */
    protected function calculateFailedSubjectsOptimized(
        Collection $term1Results,
        Collection $term2Results,
        Term $term1,
        Term $term2,
        array $termWeights
    ): array
    {
        $failedSubjects = [];

        // جمع جميع المواد من الترمين
        $allCourseOfferings = $term1Results->pluck('course_offering_id')
            ->merge($term2Results->pluck('course_offering_id'))
            ->unique();

        foreach ($allCourseOfferings as $courseOfferingId) {
            $t1 = $term1Results->firstWhere('course_offering_id', $courseOfferingId);
            $t2 = $term2Results->firstWhere('course_offering_id', $courseOfferingId);

            $totalScoreRaw = ($t1?->total_score ?? 0) + ($t2?->total_score ?? 0);

            $gradeId = $t1?->courseOffering?->classSection?->grade_id ?? $t2?->courseOffering?->classSection?->grade_id;
            $subjectId = $t1?->courseOffering?->subject_id ?? $t2?->courseOffering?->subject_id;

            if (!$gradeId || !$subjectId) {
                continue;
            }

            $termFailures = [];
            $passScore1 = $this->resolvePassScore($t1, $gradeId, $subjectId, $term1->id);
            $passScore2 = $this->resolvePassScore($t2, $gradeId, $subjectId, $term2->id);
            $weight1 = (float) ($termWeights[$term1->id] ?? 50.0);
            $weight2 = (float) ($termWeights[$term2->id] ?? 50.0);
            $passScore = ($passScore1 * ($weight1 / 100)) + ($passScore2 * ($weight2 / 100));
            $totalScore = (($t1?->total_score ?? 0) * ($weight1 / 100))
                + (($t2?->total_score ?? 0) * ($weight2 / 100));

            if ($totalScore >= $passScore) {
                continue;
            }

            if ($this->isTermFailed($t1, $gradeId, $subjectId, $term1->id)) {
                $termFailures[] = $this->buildTermFailurePayload($t1, $term1);
            }

            if ($this->isTermFailed($t2, $gradeId, $subjectId, $term2->id)) {
                $termFailures[] = $this->buildTermFailurePayload($t2, $term2);
            }

            $failedSubjects[] = [
                'course_offering_id' => $courseOfferingId,
                'subject_id' => $subjectId,
                'subject_name' => $t1?->courseOffering?->subject?->name ?? $t2?->courseOffering?->subject?->name,
                'total_score_raw' => $totalScoreRaw,
                'total_score' => $totalScore,
                'pass_score' => $passScore,
                'term_failures' => $termFailures,
            ];
        }

        return $failedSubjects;
    }

    /**
     * @return array<int, float>
     */
    private function resolveTermWeights(Collection $terms): array
    {
        $weights = SystemSetting::get('grading.term_weights', []);
        $termIds = $terms->pluck('id')->values();
        $count = $termIds->count();
        $default = $count > 0 ? (100 / $count) : 0.0;

        $resolved = [];
        $sum = 0.0;
        foreach ($termIds as $termId) {
            $weight = (float) ($weights[$termId] ?? $default);
            $resolved[$termId] = $weight;
            $sum += $weight;
        }

        if ($sum <= 0 || $count === 0) {
            return $resolved;
        }

        if (abs($sum - 100.0) > 0.01) {
            foreach ($resolved as $termId => $weight) {
                $resolved[$termId] = ($weight / $sum) * 100;
            }
        }

        return $resolved;
    }

    private function isTermFailed(?TermResult $termResult, int $gradeId, int $subjectId, int $termId): bool
    {
        if (! $termResult) {
            return false;
        }

        if ($termResult->is_passed !== null) {
            return ! $termResult->is_passed;
        }

        $passScore = $this->resolvePassScore($termResult, $gradeId, $subjectId, $termId);

        return (float) ($termResult->total_score ?? 0) < $passScore;
    }

    private function resolvePassScore(?TermResult $termResult, int $gradeId, int $subjectId, int $termId): float
    {
        if (! $termResult) {
            return 0.0;
        }

        $config = $this->getSubjectConfig($gradeId, $subjectId, $termId);
        if ($config?->pass_score !== null) {
            return (float) $config->pass_score;
        }

        $maxScore = (float) ($termResult->max_score ?? 100);

        return $maxScore * 0.5;
    }

    private function buildTermFailurePayload(?TermResult $termResult, Term $term): array
    {
        if (! $termResult) {
            return [];
        }

        return [
            'term_id' => $term->id,
            'total_score' => (float) ($termResult->total_score ?? 0),
            'max_score' => (float) ($termResult->max_score ?? 0),
            'percentage' => (float) ($termResult->percentage ?? 0),
            'failures' => $termResult->failures?->map(fn ($failure) => [
                'template_category_id' => $failure->template_category_id,
                'reason' => $failure->reason,
                'required_min' => (float) $failure->required_min,
                'actual_percentage' => (float) $failure->actual_percentage,
            ])->values()->toArray() ?? [],
        ];
    }

    /**
     * تطبيق قواعد الإكمال وحساب القرارات
     */
    public function calculateDecisions(AcademicYear $year): int
    {
        $this->healthGate->assertYearHealthy($year, 'حساب قرارات النتائج السنوية');

        $maxFailedForConditional = SystemSetting::get('promotion.max_failed_for_conditional', 3);
        $count = 0;
        $now = now();

        AnnualResult::where('academic_year_id', $year->id)
            ->where('decision', ResultDecision::Pending)
            ->select(['id', 'failed_count'])
            ->chunkById(200, function ($results) use ($maxFailedForConditional, $now, &$count) {
                foreach ($results as $result) {
                    $decision = $this->decisionStrategy->decide($result, $maxFailedForConditional);

                    DB::table('annual_results')
                        ->where('id', $result->id)
                        ->update([
                            'decision' => $decision,
                            'processed_at' => $now,
                            'updated_at' => $now,
                        ]);
                    $count++;
                }
            });

        // حساب الترتيب
        $this->calculateRanksOptimized($year);

        return $count;
    }

    /**
     * حساب الترتيب لكل صف (محسّن باستخدام CASE WHEN)
     */
    protected function calculateRanksOptimized(AcademicYear $year): void
    {
        $grades = AnnualResult::where('academic_year_id', $year->id)
            ->select('grade_id')
            ->distinct()
            ->pluck('grade_id');

        foreach ($grades as $gradeId) {
            // جلب IDs والدرجات مرتبة
            $results = AnnualResult::where('academic_year_id', $year->id)
                ->where('grade_id', $gradeId)
                ->orderByDesc('annual_total')
                ->pluck('annual_total', 'id')
                ->toArray();

            if (empty($results))
                continue;

            // حساب الترتيب
            $ranks = [];
            $rank = 1;
            $prevScore = null;
            $count = 0;

            foreach ($results as $id => $score) {
                $count++;
                if ($prevScore !== null && $score < $prevScore) {
                    $rank = $count;
                }
                $ranks[$id] = $rank;
                $prevScore = $score;
            }

            // ⚡ تحديث جماعي باستخدام CASE WHEN (استعلام واحد لكل صف)
            if (!empty($ranks)) {
                $cases = [];
                $ids = [];

                foreach ($ranks as $id => $rankValue) {
                    $cases[] = "WHEN id = {$id} THEN {$rankValue}";
                    $ids[] = $id;
                }

                $caseStatement = implode(' ', $cases);
                $idsList = implode(',', $ids);
                $now = now()->format('Y-m-d H:i:s');

                DB::statement("
                    UPDATE annual_results 
                    SET rank = CASE {$caseStatement} END,
                        updated_at = '{$now}'
                    WHERE id IN ({$idsList})
                ");
            }
        }
    }

    /**
     * جلب النتيجة السنوية لطالب
     */
    public function getStudentResult(Student $student, AcademicYear $year): ?AnnualResult
    {
        return AnnualResult::where('student_id', $student->id)
            ->where('academic_year_id', $year->id)
            ->first();
    }

    /**
     * إحصائيات النتائج لسنة معينة
     */
    public function getStatistics(AcademicYear $year): array
    {
        // ⚡ استعلام واحد للكل
        $pending = ResultDecision::Pending->value;
        $pass = ResultDecision::Pass->value;
        $conditional = ResultDecision::Conditional->value;
        $fail = ResultDecision::Fail->value;

        $stats = AnnualResult::where('academic_year_id', $year->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN decision = '{$pending}' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN decision = '{$pass}' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN decision = '{$conditional}' THEN 1 ELSE 0 END) as conditional,
                SUM(CASE WHEN decision = '{$fail}' THEN 1 ELSE 0 END) as failed,
                AVG(percentage) as average
            ")
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'pending' => $stats->pending ?? 0,
            'passed' => $stats->passed ?? 0,
            'conditional' => $stats->conditional ?? 0,
            'failed' => $stats->failed ?? 0,
            'average' => $stats->average ?? 0,
        ];
    }
}
