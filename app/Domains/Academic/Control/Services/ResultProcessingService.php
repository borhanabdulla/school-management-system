<?php

namespace App\Domains\Academic\Control\Services;

use App\Domains\Academic\Control\Models\ControlMark;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Results\Models\FinalResult;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Results\Services\TermResultFailureRecorder;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Grading\Services\SubjectScorePolicyResolver;
use App\Domains\Academic\Grading\Services\SubjectGradingConfigResolver;
use App\Domains\Academic\Grading\Services\GradingHealthGate;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Exceptions\InvalidGradingConfigException;
use App\Domains\Academic\Grading\Exceptions\MissingSubjectConfigException;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Domains\Academic\Results\Enums\FinalResultStatus;
use App\Infrastructure\Exceptions\InvalidOperationException;

class ResultProcessingService
{
    public function __construct(
        private GradingCalculatorService $calculator,
        private SubjectScorePolicyResolver $scorePolicy,
        private TermResultFailureRecorder $failureRecorder,
        private SubjectGradingConfigResolver $configResolver,
        private GradingHealthGate $healthGate
    ) {
    }

    private function resolveTerm(int $termId): Term
    {
        return $this->termCache[$termId] ??= Term::findOrFail($termId);
    }

    /**
     * @param \Illuminate\Support\Collection<int, CourseOffering> $courseOfferings
     * @return array<int, \App\Domains\Academic\Grading\Models\SubjectGradingConfig>
     */
    private function buildConfigs($courseOfferings, Term $term): array
    {
        $configs = [];

        foreach ($courseOfferings as $offering) {
            $configs[$offering->id] = $this->configResolver->resolve($offering, $term);
        }

        return $configs;
    }

    /**
     * تحديد حالة النجاح والحالة النهائية
     *
     * @return array{is_passed: bool, status: FinalResultStatus}
     */
    private function determineResultStatus(
        bool $isAbsent,
        float $totalScore,
        float $passScore,
        array $thresholdFailures
    ): array {
        $isPassed = !$isAbsent
            && $totalScore >= $passScore
            && empty($thresholdFailures);

        $status = match (true) {
            $isAbsent => FinalResultStatus::Absent,
            $isPassed => FinalResultStatus::Pass,
            default => FinalResultStatus::Fail,
        };

        return [
            'is_passed' => $isPassed,
            'status' => $status,
        ];
    }

    /**
     * @return array{total: float, grace: float}
     */
    private function applyGraceMarks(float $totalScore, float $passScore, bool $isAbsent): array
    {
        if ($isAbsent || $passScore <= 0) {
            return ['total' => $totalScore, 'grace' => 0.0];
        }

        $graceLimit = (float) SystemSetting::get('grading.grace_marks_limit', 0);
        if ($graceLimit <= 0 || $totalScore >= $passScore) {
            return ['total' => $totalScore, 'grace' => 0.0];
        }

        $needed = $passScore - $totalScore;
        $grace = min($graceLimit, $needed);

        return [
            'total' => $totalScore + $grace,
            'grace' => $grace,
        ];
    }

    /**
     * @var array<int, Term>
     */
    private array $termCache = [];
    private array $termHealthCache = [];

    private function assertTermHealthy(int $termId, string $operation): void
    {
        if (array_key_exists($termId, $this->termHealthCache)) {
            return;
        }

        $term = $this->resolveTerm($termId);
        $this->healthGate->assertTermHealthy($term, $operation);
        $this->termHealthCache[$termId] = true;
    }

    private function assertResultsNotPublished(ExamSession $session, string $operation): void
    {
        $published = FinalResult::where('exam_session_id', $session->id)
            ->where('is_published', true)
            ->exists();

        if ($published) {
            throw InvalidOperationException::cannotModify(
                $operation,
                'النتائج منشورة ويجب إعادة فتحها قبل إعادة المعالجة.'
            );
        }
    }

    /**
     * معالجة نتائج طالب واحد لجميع المواد
     */
    public function processStudent(ExamSession $session, int $studentId): Collection
    {
        $seating = ExamSeating::where('exam_session_id', $session->id)
            ->where('student_id', $studentId)
            ->first();

        if (!$seating) {
            throw new \App\Domains\Academic\Control\Exceptions\StudentNotEnrolledException($studentId, $session->id);
        }

        $results = collect();

        // جلب جميع المواد التي لها درجات كنترول لهذا الطالب
        $controlMarks = ControlMark::where('exam_seating_id', $seating->id)->get();

        foreach ($controlMarks as $controlMark) {
            $result = $this->calculateResult($session, $studentId, $controlMark->course_offering_id);
            $results->push($result);
        }

        return $results;
    }

    /**
     * معالجة نتائج مادة واحدة لجميع الطلاب
     */
    public function processSubject(ExamSession $session, int $courseOfferingId): Collection
    {
        $results = collect();

        $seatings = ExamSeating::where('exam_session_id', $session->id)
            ->whereHas('marks', fn($q) => $q->where('course_offering_id', $courseOfferingId))
            ->get();

        foreach ($seatings as $seating) {
            $result = $this->calculateResult($session, $seating->student_id, $courseOfferingId);
            $results->push($result);
        }

        return $results;
    }

    /**
     * معالجة جميع النتائج للدورة
     */
    /**
     * معالجة جميع النتائج للدورة (Optimized)
     */
    public function processAll(ExamSession $session): int
    {
        $this->assertTermHealthy($session->term_id, 'معالجة النتائج');
        $this->assertResultsNotPublished($session, 'معالجة النتائج');

        $count = 0;

        // 1. جلب جميع درجات الكنترول مع العلاقات
        $controlMarks = ControlMark::whereHas('seating', fn($q) => $q->where('exam_session_id', $session->id))
            ->with(['seating', 'courseOffering.classSection', 'courseOffering.subject'])
            ->get();

        if ($controlMarks->isEmpty()) {
            return 0;
        }

        $term = $this->resolveTerm($session->term_id);

        // 2. جلب إعدادات الدرجات (Max/Pass) لجميع المواد
        $courseOfferings = $controlMarks->pluck('courseOffering')->unique('id')->keyBy('id');

        $this->scorePolicy->preloadForCourseOfferings($courseOfferings->values(), $term->id);
        $subjectConfigs = $this->buildConfigs($courseOfferings->values(), $term);

        // 3. جلب درجات أعمال السنة لجميع الطلاب دفعة واحدة
        $studentIds = $controlMarks->pluck('seating.student_id')->unique();
        $courseOfferingIds = $courseOfferings->keys();

        $studentMarks = StudentMark::whereIn('student_id', $studentIds)
            ->whereIn('course_offering_id', $courseOfferingIds)
            ->where('term_id', $session->term_id)
            ->whereNotNull('course_offering_id')
            ->where(function ($query) {
                $query->whereNotNull('assessment_id')
                    ->orWhereNotNull('template_category_id');
            })
            ->with(['assessment.category', 'category'])
            ->get()
            ->groupBy(fn($mark) => $mark->student_id . '-' . $mark->course_offering_id);

        DB::transaction(function () use ($session, $controlMarks, $courseOfferings, $studentMarks, $subjectConfigs, &$count) {
            foreach ($controlMarks as $mark) {
                $studentId = $mark->seating->student_id;
                $courseOfferingId = $mark->course_offering_id;
                $courseOffering = $courseOfferings[$courseOfferingId];

                // حساب أعمال السنة من البيانات المحملة مسبقاً
                $key = $studentId . '-' . $courseOfferingId;
                $courseworkScore = 0;

                if (isset($studentMarks[$key])) {
                    $courseworkScore = $this->calculator
                        ->calculateCourseworkScoreFromStudentMarks($studentMarks[$key]);
                }

                $finalExamScore = $mark->score ?? 0;
                $config = $subjectConfigs[$courseOfferingId];
                $template = $config->template;

                // حساب المجموع الكلي
                $totalScoreRaw = $courseworkScore + $finalExamScore;

                $thresholds = [
                    'failures' => [],
                    'missing_thresholds' => [],
                ];


                if ($template) {
                    $template->loadMissing('categories');
                    $thresholds = $this->calculator
                        ->evaluateCategoryThresholds($template, $studentMarks[$key] ?? collect(), $finalExamScore);
                }

                // تحديد Max/Pass
                $scores = $this->scorePolicy->resolveScores($courseOffering, $session->term_id);
                $maxScore = $scores['max_score'];
                $passScore = $scores['pass_score'];

                $isAbsent = $mark->is_absent ?? false;
                $grace = $this->applyGraceMarks($totalScoreRaw, $passScore, $isAbsent);
                $totalScore = $grace['total'];
                $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
                $gradeLabel = $this->calculator->resolveGradeLabel($percentage);

                $resultStatus = $this->determineResultStatus(
                    $isAbsent,
                    $totalScore,
                    $passScore,
                    $thresholds['failures']
                );
                $isPassed = $resultStatus['is_passed'];
                $status = $resultStatus['status'];

                FinalResult::updateOrCreate(
                    [
                        'exam_session_id' => $session->id,
                        'student_id' => $studentId,
                        'course_offering_id' => $courseOfferingId,
                    ],
                    [
                        'coursework_score' => $courseworkScore,
                        'final_exam_score' => $finalExamScore,
                        'total_score' => $totalScore,
                        'grace_marks' => $grace['grace'],
                        'grade_label' => $gradeLabel,
                        'status' => $status,
                    ]
                );

                $termResult = TermResult::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'term_id' => $session->term_id,
                        'course_offering_id' => $courseOfferingId,
                    ],
                    [
                        'coursework_score' => $courseworkScore,
                        'exam_score' => $finalExamScore,
                        'total_score' => $totalScore,
                        'max_score' => $maxScore,
                        'percentage' => $percentage,
                        'is_passed' => $isPassed,
                        'calculated_at' => now(),
                    ]
                );

                $this->failureRecorder->syncFailures(
                    $termResult,
                    $thresholds['failures'] ?? []
                );

                $count++;
            }
        });

        return $count;
    }

    /**
     * حساب النتيجة النهائية لطالب ومادة واحدة
     */
    public function calculateResult(ExamSession $session, int $studentId, int $courseOfferingId): FinalResult
    {
        $this->assertTermHealthy($session->term_id, 'حساب النتيجة');
        $this->assertResultsNotPublished($session, 'حساب النتيجة');

        $term = $this->resolveTerm($session->term_id);

        // 1. جلب درجة أعمال السنة من دفتر المعلم
        $studentMarks = StudentMark::where('student_id', $studentId)
            ->where('course_offering_id', $courseOfferingId)
            ->where('term_id', $term->id)
            ->whereNotNull('course_offering_id')
            ->where(function ($query) {
                $query->whereNotNull('assessment_id')
                    ->orWhereNotNull('template_category_id');
            })
            ->with(['assessment.category', 'category'])
            ->get();

        $courseworkScore = $this->calculator
            ->calculateCourseworkScoreFromStudentMarks($studentMarks);

        // 2. جلب درجة الاختبار النهائي من الكنترول
        $seating = ExamSeating::where('exam_session_id', $session->id)
            ->where('student_id', $studentId)
            ->first();

        $controlMark = ControlMark::where('exam_seating_id', $seating?->id)
            ->where('course_offering_id', $courseOfferingId)
            ->first();

        $finalExamScore = $controlMark?->score ?? 0;
        $isAbsent = $controlMark?->is_absent ?? false;

        // 3. حساب المجموع
        $totalScoreRaw = $courseworkScore + $finalExamScore;

        // 4. تحديد درجة النجاح والتقدير
        $courseOffering = CourseOffering::with('classSection')->findOrFail($courseOfferingId);
        try {
            $config = $this->configResolver->resolve($courseOffering, $term);
        } catch (InvalidGradingConfigException | MissingSubjectConfigException $e) {
            throw InvalidOperationException::make('إعدادات الدرجات غير مكتملة');
        }
        $template = $config->template;

        $scores = $this->scorePolicy->resolveScores($courseOffering, $term->id);
        $maxScore = $scores['max_score'];
        $passScore = $scores['pass_score'];

        $thresholds = [
            'failures' => [],
            'missing_thresholds' => [],
        ];

        if ($template) {
            $template->loadMissing('categories');
            $thresholds = $this->calculator
                ->evaluateCategoryThresholds($template, $studentMarks, $finalExamScore);
        }

        $grace = $this->applyGraceMarks($totalScoreRaw, $passScore, $isAbsent);
        $totalScore = $grace['total'];
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $gradeLabel = $this->calculator->resolveGradeLabel($percentage);

        // 5. تحديد الحالة
        $resultStatus = $this->determineResultStatus(
            $isAbsent,
            $totalScore,
            $passScore,
            $thresholds['failures']
        );
        $isPassed = $resultStatus['is_passed'];
        $status = $resultStatus['status'];

        // 6. حفظ النتيجة
        $result = FinalResult::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'student_id' => $studentId,
                'course_offering_id' => $courseOfferingId,
            ],
            [
                'coursework_score' => $courseworkScore,
                'final_exam_score' => $finalExamScore,
                'total_score' => $totalScore,
                'grace_marks' => $grace['grace'],
                'grade_label' => $gradeLabel,
                'status' => $status,
            ]
        );

        $termResult = TermResult::updateOrCreate(
            [
                'student_id' => $studentId,
                'term_id' => $term->id,
                'course_offering_id' => $courseOfferingId,
            ],
            [
                'coursework_score' => $courseworkScore,
                'exam_score' => $finalExamScore,
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'is_passed' => $isPassed,
                'calculated_at' => now(),
            ]
        );

        $this->failureRecorder->syncFailures(
            $termResult,
            $thresholds['failures'] ?? []
        );

        return $result;
    }

    /**
     * نشر النتائج (جعلها مرئية للطلاب)
     */
    public function publishResults(ExamSession $session): int
    {
        $this->assertTermHealthy($session->term_id, 'نشر النتائج');

        return FinalResult::where('exam_session_id', $session->id)
            ->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
    }

    /**
     * حساب النتيجة السنوية (تجميع الترمين)
     * هذه دالة أولية للمستقبل
     */
    public function calculateAnnualResult(int $studentId, int $subjectId, int $academicYearId): array
    {
        $results = FinalResult::where('student_id', $studentId)
            ->whereHas('courseOffering', function ($q) use ($subjectId, $academicYearId) {
                $q->where('subject_id', $subjectId)
                    ->where('academic_year_id', $academicYearId);
            })
            ->with('courseOffering.term')
            ->get();

        $totalScore = $results->sum('total_score');
        $count = $results->count();
        $average = $count > 0 ? $totalScore / $count : 0;

        // افتراض: درجة النجاح السنوية هي مجموع درجات النجاح للترمين أو متوسطها
        // هنا نستخدم منطق بسيط: إذا كان المجموع >= 100 (من 200)

        return [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_year_id' => $academicYearId,
            'terms_count' => $count,
            'total_annual_score' => $totalScore,
            'average_score' => $average,
            'is_pass' => $average >= 50, // مثال بسيط
        ];
    }
}
