<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Collection;

final class AggregateGradebookToTemplateMarksAction
{
    public function __construct(
        private GradingCalculatorService $calculator
    ) {
    }

    public function execute(CourseOffering $offering, int $studentId, ?int $termId = null): void
    {
        $termId = $termId ?? $offering->term_id;
        if (! $termId) {
            return;
        }

        $term = Term::find($termId);
        if (! $term) {
            return;
        }

        app(AcademicWriteGuard::class)->assertTermNotCompleted($termId);

        $gradeId = $offering->classSection?->grade_id;
        $subjectId = $offering->subject_id;
        $academicYearId = $term->academic_year_id;

        if (! $gradeId || ! $subjectId || ! $academicYearId) {
            return;
        }

        $mappings = MonthlyCategoryMapping::query()
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('grade_id', $gradeId)
            ->where('subject_id', $subjectId)
            ->get();

        if ($mappings->isEmpty()) {
            return;
        }

        $months = GradebookMonth::where('term_id', $termId)
            ->orderBy('order')
            ->get(['id', 'order', 'start_date', 'end_date']);

        if ($months->isEmpty()) {
            return;
        }

        $monthIds = $months->pluck('id');
        $templateCategoryIds = $mappings->pluck('template_category_id')->unique()->values();
        if ($templateCategoryIds->isEmpty()) {
            return;
        }

        $grades = MonthlyGrade::query()
            ->where('student_id', $studentId)
            ->where('course_offering_id', $offering->id)
            ->whereIn('gradebook_month_id', $monthIds)
            ->whereIn('template_category_id', $templateCategoryIds)
            ->get(['template_category_id', 'category_key', 'score', 'max_score', 'gradebook_month_id']);

        $gradesByCategory = $grades->groupBy('template_category_id');

        $settings = GradebookSettings::where('academic_year_id', $academicYearId)->first();
        $categories = $settings
            ? GradebookSettings::normalizeMonthlyCategories($settings->monthly_categories ?? [])
            : [];
        $categoryDefaults = collect($categories)->keyBy('key');

        $templateCategories = TemplateCategory::whereIn('id', $mappings->pluck('template_category_id'))
            ->get()
            ->keyBy('id');

        foreach ($mappings as $mapping) {
            $categoryKey = (string) $mapping->category_key;
            if ($categoryKey === '') {
                continue;
            }

            $templateCategoryId = (int) $mapping->template_category_id;
            $templateCategory = $templateCategories->get($templateCategoryId);
            if (! $templateCategory) {
                continue;
            }

            $defaultMax = (float) ($categoryDefaults->get($categoryKey)['max_score'] ?? 0);
            $aggregation = $this->aggregate(
                $gradesByCategory->get($templateCategoryId, collect()),
                $months,
                (string) $mapping->aggregation_rule,
                (string) $mapping->missing_months_policy,
                $defaultMax
            );

            $normalized = 0.0;
            if ($aggregation['max'] > 0) {
                $normalized = $this->calculator->normalize(
                    rawScore: $aggregation['raw'],
                    maxScore: $aggregation['max'],
                    weight: (float) $templateCategory->weight
                );
            }

            StudentMark::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'course_offering_id' => $offering->id,
                    'template_category_id' => $templateCategory->id,
                    'term_id' => $termId,
                ],
                [
                    'raw_score' => $aggregation['raw'],
                    'scaled_score' => $normalized,
                    'graded_by_user_id' => null,
                    'academic_year_id' => $academicYearId,
                    'term_id' => $termId,
                    'is_missing' => ! $aggregation['has_entries'],
                ]
            );
        }
    }

    /**
     * @return array{raw: float, max: float, has_entries: bool}
     */
    private function aggregate(
        Collection $entries,
        Collection $months,
        string $rule,
        string $missingPolicy,
        float $defaultMax
    ): array {
        $entriesByMonth = $entries
            ->filter(fn ($entry) => $entry->gradebook_month_id !== null)
            ->groupBy('gradebook_month_id');

        $monthTotals = $entriesByMonth->map(function ($rows) use ($defaultMax) {
            $score = 0.0;
            $max = 0.0;

            foreach ($rows as $entry) {
                $score += (float) ($entry->score ?? 0);
                $max += (float) ($entry->max_score ?? $defaultMax);
            }

            return [
                'score' => $score,
                'max' => $max,
            ];
        });

        $hasEntries = $monthTotals->isNotEmpty();
        $monthCount = $months->count();
        $missingCount = max(0, $monthCount - $monthTotals->count());

        $totalScore = 0.0;
        $totalMax = 0.0;

        foreach ($monthTotals as $monthTotal) {
            $totalScore += $monthTotal['score'];
            $totalMax += $monthTotal['max'];
        }

        if ($missingPolicy === 'zero' && $missingCount > 0) {
            $totalMax += $missingCount * $defaultMax;
        }

        switch ($rule) {
            case 'avg':
                $divisor = $missingPolicy === 'zero' ? max(1, $monthCount) : max(1, $monthTotals->count());
                $raw = $totalScore / $divisor;
                $max = $totalMax / $divisor;
                break;
            case 'last':
                $raw = 0.0;
                $max = $defaultMax;
                foreach ($months->sortByDesc('order') as $month) {
                    $monthTotal = $monthTotals->get($month->id);
                    if ($monthTotal) {
                        $raw = (float) $monthTotal['score'];
                        $max = (float) $monthTotal['max'];
                        break;
                    }
                }
                break;
            case 'weighted':
                $raw = $totalScore;
                $max = $totalMax;
                break;
            case 'sum':
            default:
                $raw = $totalScore;
                $max = $totalMax;
                break;
        }

        return [
            'raw' => $raw,
            'max' => $max,
            'has_entries' => $hasEntries,
        ];
    }
}
