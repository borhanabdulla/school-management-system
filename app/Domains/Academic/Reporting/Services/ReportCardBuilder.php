<?php

namespace App\Domains\Academic\Reporting\Services;

use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Reporting\Support\GradeLabelResolver;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Collection;
use App\Domains\Finance\Services\StudentFinancialClearanceService;
use App\Domains\Finance\Exceptions\ResultsBlockedByFinanceException;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\SystemSetting;

final class ReportCardBuilder
{
    private StudentFinancialClearanceService $clearanceService;

    public function __construct(StudentFinancialClearanceService $clearanceService)
    {
        $this->clearanceService = $clearanceService;
    }

    public function buildForStudents(Collection $students, int $termId, ?int $classSectionId = null): array
    {
        if ($students->isEmpty()) {
            return [];
        }

        // PR4.1: Financial Clearance Batch Check
        $academicYearId = Term::where('id', $termId)->value('academic_year_id');
        $studentIds = $students->pluck('id')->filter()->values()->toArray();
        if (empty($studentIds)) {
            return [];
        }

        $clearanceStatus = $this->clearanceService->checkMany($studentIds, $academicYearId);

        $query = TermResult::with([
            'courseOffering.subject',
            'courseOffering.classSection.grade',
            'term',
            'student',
            'failures',
        ])->whereIn('student_id', $studentIds)
            ->where('term_id', $termId);

        if ($classSectionId) {
            $query->whereHas('courseOffering', fn($q) => $q->where('class_section_id', $classSectionId));
        }

        $results = $query->get();
        $passScoresByGradeSubject = $this->buildPassScoreMap($results);
        $resultsByStudent = $results->groupBy('student_id');

        $reports = [];

        foreach ($students as $student) {
            // Priority Check: Financial Clearance
            $status = $clearanceStatus[$student->id] ?? ['is_cleared' => true]; // Fallback to cleared if not found (unexpected)

            if (!$status['is_cleared']) {
                $reports[$student->id] = [
                    'meta' => [
                        'student' => [
                            'id' => $student->id,
                            'full_name_ar' => $student->full_name_ar,
                            'class_section' => $student->currentClassSection?->name,
                            'grade' => $student->currentClassSection?->grade?->name,
                        ],
                        'is_blocked' => true,
                        'blocking_reason' => $status['reason'],
                    ],
                    'subjects' => [],
                    'totals' => [],
                ];
                continue;
            }

            $studentResults = $resultsByStudent->get($student->id, collect());

            if ($studentResults->isEmpty()) {
                $reports[$student->id] = [
                    'meta' => [
                        'student' => [
                            'id' => $student->id,
                            'full_name_ar' => $student->full_name_ar,
                            'class_section' => $student->currentClassSection?->name,
                            'grade' => $student->currentClassSection?->grade?->name,
                        ],
                        'term' => null,
                    ],
                    'subjects' => [],
                    'totals' => [
                        'total_score' => 0,
                        'max_score' => 0,
                        'percentage' => 0,
                        'passed_count' => 0,
                        'failed_count' => 0,
                        'grade_label' => '-',
                    ],
                ];
                continue;
            }

            $subjects = $this->buildSubjects($studentResults, $passScoresByGradeSubject);
            $totals = $this->buildTotals($subjects);
            $firstResult = $studentResults->first();

            $reports[$student->id] = [
                'meta' => [
                    'student' => [
                        'id' => $student->id,
                        'full_name_ar' => $student->full_name_ar,
                        'class_section' => $firstResult?->courseOffering?->classSection?->name
                            ?? $student->currentClassSection?->name,
                        'grade' => $firstResult?->courseOffering?->classSection?->grade?->name
                            ?? $student->currentClassSection?->grade?->name,
                    ],
                    'term' => $firstResult?->term ? [
                        'id' => $firstResult->term->id,
                        'name' => $firstResult->term->name,
                    ] : null,
                    'is_blocked' => false, // Contract: Always present
                ],
                'subjects' => $subjects,
                'totals' => $totals,
            ];
        }

        return $reports;
    }

    public function build(int $studentId, int $termId, ?int $classSectionId = null): array
    {
        // PR4.1: Financial Clearance Single Check
        $academicYearId = Term::where('id', $termId)->value('academic_year_id');
        $clearance = $this->clearanceService->checkClearance($studentId, $academicYearId);

        if (!$clearance['is_cleared']) {
            throw new ResultsBlockedByFinanceException($clearance['reason']);
        }

        $query = TermResult::with([
            'courseOffering.subject',
            'courseOffering.classSection.grade',
            'term',
            'student',
            'failures',
        ])->where('student_id', $studentId)
            ->where('term_id', $termId);

        if ($classSectionId) {
            $query->whereHas('courseOffering', fn($q) => $q->where('class_section_id', $classSectionId));
        }

        $results = $query->get();

        $passScoresByGradeSubject = $this->buildPassScoreMap($results);
        $subjects = $this->buildSubjects($results, $passScoresByGradeSubject);
        $totals = $this->buildTotals($subjects);

        $firstResult = $results->first();

        return [
            'meta' => [
                'student' => $firstResult?->student ? [
                    'id' => $firstResult->student->id,
                    'full_name_ar' => $firstResult->student->full_name_ar,
                    'class_section' => optional($firstResult->courseOffering->classSection)->name,
                    'grade' => optional($firstResult->courseOffering->classSection->grade)->name,
                ] : null,
                'term' => $firstResult?->term ? [
                    'id' => $firstResult->term->id,
                    'name' => $firstResult->term->name,
                ] : null,
            ],
            'subjects' => $subjects,
            'totals' => $totals,
        ];
    }

    private function buildSubjects(Collection $results, array $passScoresByGradeSubject): array
    {
        return $results->map(function (TermResult $result) use ($passScoresByGradeSubject) {
            $subjectId = $result->courseOffering->subject->id;
            $gradeId = $result->courseOffering->classSection->grade_id;
            $passScoreKey = $gradeId . ':' . $subjectId;
            $defaultPassPercent = (float) SystemSetting::get('grading.default_pass_score', 50);
            $defaultPassPercent = max(0.0, min(100.0, $defaultPassPercent));
            $fallbackPassScore = $result->max_score > 0
                ? $result->max_score * ($defaultPassPercent / 100)
                : 0.0;

            return [
                'course_offering_id' => $result->course_offering_id,
                'subject' => [
                    'id' => $subjectId,
                    'name' => $result->courseOffering->subject->name,
                ],
                'coursework_score' => $result->coursework_score,
                'exam_score' => $result->exam_score,
                'total_score' => $result->total_score,
                'max_score' => $result->max_score,
                'percentage' => $result->percentage,
                'grade_label' => $this->resolveGradeLabel($result->grade_letter, $result->percentage),
                'is_passed' => $result->is_passed,
                'failures' => $result->failures->map(fn($failure) => [
                    'category_id' => $failure->template_category_id,
                    'message' => $failure->reason,
                ])->toArray(),
                'pass_score' => $passScoresByGradeSubject[$passScoreKey] ?? $fallbackPassScore,
            ];
        })->toArray();
    }

    private function buildPassScoreMap(Collection $results): array
    {
        if ($results->isEmpty()) {
            return [];
        }

        $gradeIds = $results->pluck('courseOffering.classSection.grade_id')->filter()->unique()->values();
        $subjectIds = $results->pluck('courseOffering.subject.id')->filter()->unique()->values();
        $termId = $results->first()?->term_id;

        if (! $termId) {
            return [];
        }

        return SubjectGradingConfig::query()
            ->where('term_id', $termId)
            ->whereIn('grade_id', $gradeIds)
            ->whereIn('subject_id', $subjectIds)
            ->get(['grade_id', 'subject_id', 'pass_score'])
            ->mapWithKeys(function (SubjectGradingConfig $config) {
                return [
                    $config->grade_id . ':' . $config->subject_id => $config->pass_score,
                ];
            })
            ->all();
    }

    private function resolveGradeLabel(?string $gradeLetter, ?float $percentage): string
    {
        if ($gradeLetter) {
            return $gradeLetter;
        }

        return GradeLabelResolver::forPercentage((float) $percentage);
    }

    private function buildTotals(array $subjects): array
    {
        $totalScore = array_sum(array_column($subjects, 'total_score'));
        $maxScore = array_sum(array_column($subjects, 'max_score'));
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $passedCount = count(array_filter($subjects, fn($subject) => $subject['is_passed']));
        $failedCount = count(array_filter($subjects, fn($subject) => !$subject['is_passed']));

        return [
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'grade_label' => GradeLabelResolver::forPercentage($percentage),
        ];
    }
}
