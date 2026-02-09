<?php

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\Grading\Data\MonthlyMappingData;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Term\Models\Term;

class MonthlyMappingService
{
    public function loadMappings(int $subjectId, int $gradeId, int $termId): array
    {
        $term = Term::find($termId);
        if (! $term) {
            return [
                'categories' => [],
                'templateCategories' => [],
                'mappings' => [],
            ];
        }

        $settings = GradebookSettings::where('academic_year_id', $term->academic_year_id)->first();
        $categories = $settings
            ? GradebookSettings::normalizeMonthlyCategories($settings->monthly_categories ?? [])
            : [];

        $config = SubjectGradingConfig::where('subject_id', $subjectId)
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->with('template.categories')
            ->first();

        if (! $config || ! $config->template) {
            return [
                'categories' => $categories,
                'templateCategories' => [],
                'mappings' => [],
            ];
        }

        $templateCategories = $config->template->categories ?? collect();
        $templateOptions = $this->flattenTemplateCategories($templateCategories);

        $existing = MonthlyCategoryMapping::query()
            ->where('academic_year_id', $term->academic_year_id)
            ->where('term_id', $termId)
            ->where('grade_id', $gradeId)
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('category_key');

        $mappings = [];
        foreach ($categories as $category) {
            $key = (string) ($category['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $row = $existing->get($key);
            $mappings[$key] = [
                'template_category_id' => $row?->template_category_id,
                'aggregation_rule' => $row?->aggregation_rule ?? 'sum',
                'missing_months_policy' => $row?->missing_months_policy ?? 'ignore',
            ];
        }

        return [
            'categories' => $categories,
            'templateCategories' => $templateOptions,
            'mappings' => $mappings,
        ];
    }

    public function saveMappings(
        MonthlyMappingData $data,
        array $aggregationRuleOptions,
        array $missingMonthsPolicyOptions
    ): void {
        $config = SubjectGradingConfig::where('subject_id', $data->subjectId)
            ->where('grade_id', $data->gradeId)
            ->where('term_id', $data->termId)
            ->with('template.categories')
            ->first();

        if (! $config || ! $config->template) {
            throw new \InvalidArgumentException('لا توجد إعدادات قالب للمادة المحددة.');
        }

        $validCategoryIds = $config->template->categories
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($data->mappings as $payload) {
            $templateCategoryId = $payload['template_category_id'] ?? null;
            if ($templateCategoryId && ! in_array((int) $templateCategoryId, $validCategoryIds, true)) {
                throw new \InvalidArgumentException('الفئة المختارة لا تتبع قالب هذه المادة.');
            }
        }

        $existing = MonthlyCategoryMapping::query()
            ->where('academic_year_id', $data->academicYearId)
            ->where('term_id', $data->termId)
            ->where('grade_id', $data->gradeId)
            ->where('subject_id', $data->subjectId)
            ->get()
            ->keyBy('category_key');

        foreach ($data->mappings as $key => $payload) {
            $templateCategoryId = $payload['template_category_id'] ?? null;
            $aggregationRule = $payload['aggregation_rule'] ?? 'sum';
            $missingPolicy = $payload['missing_months_policy'] ?? 'ignore';

            if (! array_key_exists($aggregationRule, $aggregationRuleOptions)) {
                $aggregationRule = 'sum';
            }

            if (! array_key_exists($missingPolicy, $missingMonthsPolicyOptions)) {
                $missingPolicy = 'ignore';
            }

            if ($templateCategoryId) {
                MonthlyCategoryMapping::updateOrCreate(
                    [
                        'academic_year_id' => $data->academicYearId,
                        'term_id' => $data->termId,
                        'grade_id' => $data->gradeId,
                        'subject_id' => $data->subjectId,
                        'category_key' => $key,
                    ],
                    [
                        'template_category_id' => $templateCategoryId,
                        'aggregation_rule' => $aggregationRule,
                        'missing_months_policy' => $missingPolicy,
                    ]
                );
            } else {
                $existingRow = $existing->get($key);
                if ($existingRow) {
                    $existingRow->delete();
                }
            }
        }
    }

    private function flattenTemplateCategories($categories): array
    {
        $byParent = $categories->groupBy('parent_id');
        $options = [];

        $walk = function (?int $parentId, string $prefix) use (&$walk, $byParent, &$options): void {
            foreach ($byParent->get($parentId, collect()) as $category) {
                $options[] = [
                    'value' => $category->id,
                    'label' => $prefix . $category->name,
                ];
                $walk($category->id, $prefix . '— ');
            }
        };

        $walk(null, '');

        return $options;
    }
}
