<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Services\SubjectScorePolicyResolver;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\SubjectGradingConfigResolver;
use App\Domains\Academic\Grading\Services\GradingHealthGate;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Results\Services\FinalExamScoreResolver;
use App\Domains\Academic\Results\Services\TermResultFailureRecorder;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Support\Facades\DB;

/**
 * CalculateTermGradesAction - حساب درجات الفصل الدراسي
 * 
 * يحسب ويحفظ النتائج النهائية للطلاب في فصل دراسي معين
 */
class CalculateTermGradesAction
{
    public function __construct(
        private GradingCalculatorService $calculator,
        private FinalExamScoreResolver $finalExamScoreResolver,
        private SubjectScorePolicyResolver $scorePolicy,
        private TermResultFailureRecorder $failureRecorder,
        private SubjectGradingConfigResolver $configResolver,
        private GradingHealthGate $healthGate
    ) {
    }

    /**
     * حساب درجات شعبة كاملة
     *
     * @param ClassSection $classSection الشعبة
     * @param Term $term الفصل الدراسي
     * @return array{processed: int, failed: int, results: array}
     */
    public function execute(ClassSection $classSection, Term $term): array
    {
        // 🛡️ PR-1: Guard against closed term (and year)
        app(AcademicWriteGuard::class)->assertTermNotCompleted($term->id);
        $this->healthGate->assertTermHealthy($term, 'حساب نتائج الترم');
        
        $studentIds = StudentEnrollment::query()
            ->where('academic_year_id', $term->academic_year_id)
            ->where('class_section_id', $classSection->id)
            ->pluck('student_id')
            ->unique();

        $students = $studentIds->isEmpty()
            ? collect()
            : Student::query()
                ->whereIn('id', $studentIds)
                ->active()
                ->get();
        $courseOfferings = CourseOffering::where('class_section_id', $classSection->id)
            ->where('term_id', $term->id)
            ->with(['subject', 'classSection'])
            ->get();

        $this->scorePolicy->preloadForCourseOfferings($courseOfferings, $term->id);
        $subjectConfigs = $this->buildConfigs($courseOfferings, $term);

        $processed = 0;
        $failed = 0;
        $results = [];

        DB::transaction(function () use ($students, $courseOfferings, $term, $subjectConfigs, &$processed, &$failed, &$results) {
            foreach ($students as $student) {
                try {
                    $studentResults = $this->calculateStudentGrades($student, $courseOfferings, $term, $subjectConfigs);
                    $results[$student->id] = $studentResults;
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    $results[$student->id] = ['error' => $e->getMessage()];
                }
            }
        });

        return [
            'processed' => $processed,
            'failed' => $failed,
            'results' => $results,
        ];
    }

    /**
     * حساب درجات طالب واحد
     */
    private function calculateStudentGrades(
        Student $student,
        $courseOfferings,
        Term $term,
        array $subjectConfigs
    ): array {
        $subjectResults = [];
        $totalScore = 0;
        $totalMax = 0;

        foreach ($courseOfferings as $courseOffering) {
            $config = $subjectConfigs[$courseOffering->id];
            $grade = $this->calculateSubjectGrade($student, $courseOffering, $term, $config);

            // حفظ النتيجة
            $termResult = TermResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'term_id' => $term->id,
                    'course_offering_id' => $courseOffering->id,
                ],
                [
                    'coursework_score' => $grade['coursework'] ?? 0,
                    'exam_score' => $grade['exam'] ?? 0,
                    'total_score' => $grade['total'],
                    'max_score' => $grade['max'],
                    'percentage' => $grade['percentage'] ?? 0,
                    'grade_letter' => $grade['letter'],
                    'is_passed' => $grade['passed'],
                    'calculated_at' => now(),
                ]
            );

            $this->failureRecorder->syncFailures(
                $termResult,
                $grade['threshold_failures'] ?? []
            );

            $subjectResults[$courseOffering->subject->name] = $grade;
            $totalScore += $grade['total'];
            $totalMax += $grade['max'];
        }

        return [
            'subjects' => $subjectResults,
            'average' => $totalMax > 0 ? round(($totalScore / $totalMax) * 100, 2) : 0,
            'passed' => $this->checkOverallPass($subjectResults),
        ];
    }

    /**
     * حساب درجة مادة واحدة
     */
    private function calculateSubjectGrade(
        Student $student,
        CourseOffering $courseOffering,
        Term $term,
        SubjectGradingConfig $config
    ): array
    {
        $marks = StudentMark::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where(function ($query) use ($courseOffering) {
                $query->where('course_offering_id', $courseOffering->id)
                    ->orWhereHas('assessment', fn($q) => $q->where('course_offering_id', $courseOffering->id));
            })
            ->with(['category', 'assessment.category'])
            ->get();

        $courseworkScore = $this->calculator
            ->calculateCourseworkScoreFromStudentMarks($marks);

        $examScore = $this->finalExamScoreResolver
            ->resolveForTerm($student->id, $courseOffering->id, $term->id);

        $scores = $this->scorePolicy->resolveScores($courseOffering, $term->id);
        $maxScore = $scores['max_score'];
        $passScore = $scores['pass_score'];

        $template = $config->template;
        $thresholds = [
            'failures' => [],
            'missing_thresholds' => [],
        ];

        if ($template) {
            $template->loadMissing('categories');
            $thresholds = $this->calculator
                ->evaluateCategoryThresholds($template, $marks, $examScore);
        }

        $totalScore = $courseworkScore + $examScore;
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        return [
            'coursework' => $courseworkScore,
            'exam' => $examScore,
            'total' => $totalScore,
            'max' => $maxScore,
            'percentage' => $percentage,
            'letter' => $this->calculator->resolveGradeLabel($percentage),
            'passed' => $totalScore >= $passScore && empty($thresholds['failures']),
            'threshold_failures' => $thresholds['failures'],
            'missing_thresholds' => $thresholds['missing_thresholds'],
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, CourseOffering> $courseOfferings
     * @return array<int, SubjectGradingConfig>
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
     * التحقق من النجاح الكلي
     */
    private function checkOverallPass(array $subjectResults): bool
    {
        foreach ($subjectResults as $result) {
            if (!$result['passed']) {
                return false;
            }
        }
        return true;
    }
}
