<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GradingCalculatorService
{
    public function __construct(
        private GradingLookupService $lookupService
    ) {
    }

    /**
     * AGS Normalization Formula
     * 
     * normalized = (raw / max) * weight
     * 
     * @param float $rawScore
     * @param float $maxScore
     * @param float $weight
     * @return float
     */
    public function normalize(float $rawScore, float $maxScore, float $weight): float
    {
        if ($maxScore <= 0) {
            return 0;
        }

        return round(($rawScore / $maxScore) * $weight, 2);
    }

    /**
     * Calculate Attendance Score based on absence count and settings.
     * 
     * @param int $absenceCount
     * @param GradebookSettings $settings
     * @return float
     */
    public function calculateAttendanceScore(int $absenceCount, GradebookSettings $settings): float
    {
        $maxScore = (float) $settings->attendance_max_score;
        $deductAfter = (int) $settings->attendance_deduct_after;
        $deductPerAbsence = (float) $settings->attendance_deduct_per_absence;

        if ($absenceCount <= $deductAfter) {
            return $maxScore;
        }

        $deductibleAbsences = $absenceCount - $deductAfter;
        $deduction = $deductibleAbsences * $deductPerAbsence;

        return max(0, $maxScore - $deduction);
    }

    public function calculateStudentGrade(
        Student $student,
        CourseOffering $courseOffering,
        GradingTemplate $template,
        ?Collection $marks = null,
        ?float $passScore = null
    ): array {
        if ($marks === null) {
            throw new InvalidArgumentException('StudentMarks collection is required.');
        }

        [$categoryResults, $totalScore, $totalMax, $roundingRule, $roundingPrecision] = $this->buildCategoryResults(
            $template,
            $marks
        );

        $percentage = $totalMax > 0 ? ($totalScore / $totalMax) * 100 : 0;
        $percentage = $this->applyRounding($percentage, $roundingRule, $roundingPrecision);

        $passThreshold = $passScore ?? (float) ($template->pass_score ?? 0);

        return [
            'total' => $totalScore,
            'score' => $totalScore,
            'max' => $totalMax,
            'max_score' => $totalMax,
            'percentage' => $percentage,
            'passed' => $totalScore >= $passThreshold,
            'categories' => $categoryResults,
        ];
    }

    public function resolveGradeLabel(float $percentage): string
    {
        $scale = $this->lookupService->getDefaultGradeScale() ?? [];
        $entries = array_values(array_filter($scale, function ($entry) {
            return is_array($entry)
                && array_key_exists('min', $entry)
                && array_key_exists('max', $entry)
                && array_key_exists('grade', $entry);
        }));

        if ($entries === []) {
            return $this->resolveDefaultGradeLabel($percentage);
        }

        usort($entries, function ($left, $right) {
            return (float) ($left['min'] ?? 0) <=> (float) ($right['min'] ?? 0);
        });

        foreach ($entries as $entry) {
            $min = (float) $entry['min'];
            $max = (float) $entry['max'];

            if ($percentage >= $min && $percentage <= $max) {
                return (string) $entry['grade'];
            }
        }

        $first = $entries[0];
        $last = $entries[count($entries) - 1];

        if ($percentage < (float) $first['min']) {
            return (string) $first['grade'];
        }

        if ($percentage > (float) $last['max']) {
            return (string) $last['grade'];
        }

        $closest = null;
        foreach ($entries as $entry) {
            if ($percentage >= (float) $entry['max']) {
                $closest = $entry;
            }
        }

        return (string) (($closest['grade'] ?? null) ?: $last['grade'] ?? $this->resolveDefaultGradeLabel($percentage));
    }

    /**
     * Evaluate category-level thresholds (pass_required/pass_threshold).
     *
     * @return array{
     *   failed: bool,
     *   failures: array<int, array{category_id: int, threshold: float, actual_percentage: float}>,
     *   missing_thresholds: array<int, int>
     * }
     */
    public function evaluateCategoryThresholds(
        GradingTemplate $template,
        Collection $marks,
        float $finalExamScore = 0.0
    ): array {
        [$categoryResults, , , $roundingRule, $roundingPrecision] = $this->buildCategoryResults(
            $template,
            $marks
        );

        $flatResults = $this->flattenCategoryResults($categoryResults);
        $failures = [];
        $missingThresholds = [];

        foreach ($template->getRelation('categories') as $category) {
            if (! $category->pass_required) {
                continue;
            }

            if ($category->pass_threshold === null) {
                $missingThresholds[] = $category->id;
                continue;
            }

            $threshold = (float) $category->pass_threshold;
            $actualPercentage = 0.0;

            if ($category->is_final_exam) {
                $maxScore = (float) ($category->max_raw_score ?? 0);
                $actualPercentage = $maxScore > 0 ? ($finalExamScore / $maxScore) * 100 : 0.0;
                $actualPercentage = $this->applyRounding($actualPercentage, $roundingRule, $roundingPrecision);
            } else {
                $actualPercentage = (float) ($flatResults[$category->id]['percentage'] ?? 0.0);
            }

            if ($actualPercentage < $threshold) {
                $failures[] = [
                    'category_id' => $category->id,
                    'threshold' => $threshold,
                    'actual_percentage' => $actualPercentage,
                ];
            }
        }

        return [
            'failed' => ! empty($failures),
            'failures' => $failures,
            'missing_thresholds' => $missingThresholds,
        ];
    }

    /**
     * Compute coursework score from already-loaded StudentMarks.
     * Excludes final-exam categories using explicit flags only.
     */
    public function calculateCourseworkScoreFromStudentMarks(Collection $marks): float
    {
        $total = 0.0;

        foreach ($marks as $mark) {
            $category = $mark->category ?? $mark->assessment?->category;

            if (! $category) {
                throw new \RuntimeException('StudentMark missing category relation.');
            }

            if ($category->is_final_exam) {
                continue;
            }

            $total += (float) ($mark->scaled_score ?? 0);
        }

        return $total;
    }

    /**
     * @return array{0: array, 1: float, 2: float, 3: string, 4: int}
     */
    private function buildCategoryResults(GradingTemplate $template, Collection $marks): array
    {
        if (! $template->relationLoaded('categories')) {
            throw new InvalidArgumentException('Template categories must be loaded.');
        }

        $categories = $template->getRelation('categories');
        $categoryIds = $categories->pluck('id')->flip();
        foreach ($categories as $category) {
            if ($category->parent_id !== null && ! $categoryIds->has($category->parent_id)) {
                throw new InvalidArgumentException('Template categories must include full hierarchy.');
            }
        }

        $childrenByParent = $categories->groupBy(function ($category) {
            return $category->parent_id === null ? 'root' : (string) $category->parent_id;
        });

        $marksByCategory = [];
        foreach ($marks as $mark) {
            $category = $mark->relationLoaded('category')
                ? $mark->getRelation('category')
                : null;

            if (! $category && $mark->relationLoaded('assessment')) {
                $assessment = $mark->getRelation('assessment');
                if ($assessment && ! $assessment->relationLoaded('category')) {
                    throw new InvalidArgumentException('Assessment category must be loaded.');
                }
                $category = $assessment?->getRelation('category');
            }

            if (! $category) {
                throw new \RuntimeException('StudentMark missing category relation.');
            }

            $marksByCategory[$category->id][] = $mark;
        }

        $roundingRule = $template->rounding_rule ?? 'nearest_integer';
        $roundingPrecision = (int) ($template->rounding_precision ?? 0);

        $categoryResults = [];
        $totalScore = 0.0;
        $totalMax = 0.0;

        foreach ($childrenByParent->get('root', collect()) as $category) {
            $result = $this->calculateCategoryResult(
                $category,
                $marksByCategory,
                $roundingRule,
                $roundingPrecision,
                $childrenByParent
            );

            $categoryResults[$category->id] = $result;
            $totalScore += $result['score'];
            $totalMax += (float) $category->weight;
        }

        return [$categoryResults, $totalScore, $totalMax, $roundingRule, $roundingPrecision];
    }

    private function flattenCategoryResults(array $categoryResults): array
    {
        $flat = [];

        foreach ($categoryResults as $categoryId => $result) {
            $flat[$categoryId] = $result;
            $children = $result['children'] ?? [];
            if (! empty($children)) {
                foreach ($this->flattenCategoryResults($children) as $childId => $childResult) {
                    $flat[$childId] = $childResult;
                }
            }
        }

        return $flat;
    }

    private function calculateCategoryResult(
        TemplateCategory $category,
        array $marksByCategory,
        string $roundingRule,
        int $roundingPrecision,
        Collection $childrenByParent
    ): array {
        $children = $childrenByParent->get((string) $category->id, collect());

        if ($children->isNotEmpty()) {
            $childResults = [];
            $weightSum = 0.0;
            $weightedPercentage = 0.0;

            foreach ($children as $child) {
                $result = $this->calculateCategoryResult(
                    $child,
                    $marksByCategory,
                    $roundingRule,
                    $roundingPrecision,
                    $childrenByParent
                );

                $childResults[$child->id] = $result;
                $weightSum += (float) $child->weight;
                $weightedPercentage += $result['percentage'] * (float) $child->weight;
            }

            $percentage = $weightSum > 0 ? $weightedPercentage / $weightSum : 0.0;
            $percentage = $this->applyRounding($percentage, $roundingRule, $roundingPrecision);

            return [
                'percentage' => $percentage,
                'score' => $percentage * ((float) $category->weight / 100),
                'weight' => (float) $category->weight,
                'children' => $childResults,
            ];
        }

        $marks = $marksByCategory[$category->id] ?? [];
        $totalRaw = 0.0;
        $totalMax = 0.0;
        $hasManual = false;

        foreach ($marks as $mark) {
            $assessment = $mark->relationLoaded('assessment')
                ? $mark->getRelation('assessment')
                : null;

            if ($assessment) {
                $totalRaw += (float) ($mark->raw_score ?? 0);
                $totalMax += (float) ($assessment->max_score ?? 0);
                continue;
            }

            $totalRaw += (float) ($mark->raw_score ?? $mark->scaled_score ?? 0);
            $hasManual = true;
        }

        if ($hasManual) {
            $manualMax = (float) ($category->max_raw_score ?? 0);
            if ($manualMax > 0) {
                $totalMax += $manualMax;
            }
        }

        $categoryMax = (float) ($category->max_raw_score ?? 0);
        if ($categoryMax > 0 && $totalMax > $categoryMax) {
            $totalMax = $categoryMax;
            $totalRaw = min($totalRaw, $totalMax);
        }

        $percentage = $totalMax > 0 ? ($totalRaw / $totalMax) * 100 : 0.0;
        $percentage = $this->applyRounding($percentage, $roundingRule, $roundingPrecision);

        return [
            'percentage' => $percentage,
            'score' => $percentage * ((float) $category->weight / 100),
            'weight' => (float) $category->weight,
            'children' => [],
        ];
    }

    private function applyRounding(float $value, string $rule, int $precision): float
    {
        $precision = max(0, $precision);
        $factor = 10 ** $precision;

        return match ($rule) {
            'none' => $value,
            'up' => ceil($value * $factor) / $factor,
            'down' => floor($value * $factor) / $factor,
            'nearest_integer' => round($value, 0, PHP_ROUND_HALF_UP),
            default => round($value, $precision, PHP_ROUND_HALF_UP),
        };
    }

    private function resolveDefaultGradeLabel(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'ممتاز',
            $percentage >= 80 => 'جيد جداً',
            $percentage >= 70 => 'جيد',
            $percentage >= 60 => 'مقبول',
            $percentage >= 50 => 'ضعيف',
            default => 'راسب',
        };
    }
}
