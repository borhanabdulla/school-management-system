<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Validators;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\Health\GradingConfigValidator;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;

final class TemplateIntegrityValidator implements GradingConfigValidator
{
    public function validate(CourseOffering $offering, SubjectGradingConfig $config): array
    {
        $template = $config->template;
        if (! $template) {
            return [];
        }

        $issues = [];
        $rootCategories = $template
            ->categories
            ->filter(fn ($category) => $category->parent_id === null);

        if ($rootCategories->isEmpty()) {
            $issues[] = new GradingConfigViolation(
                'NO_ROOT_CATEGORIES',
                'القالب لا يحتوي على فئات جذرية.',
                ['template_id' => $template->id]
            );
            return $issues;
        }

        $weightSum = (float) $rootCategories->sum('weight');

        if (abs($weightSum - 100.0) > 0.01) {
            $issues[] = new GradingConfigViolation(
                'WEIGHTS_NOT_100',
                sprintf('مجموع الأوزان الجذرية = %.2f%% (يجب أن يساوي 100).', $weightSum),
                ['template_id' => $template->id, 'weight_sum' => $weightSum]
            );
        }

        $finalExamCategories = $template->categories->filter(fn ($category) => $category->is_final_exam);
        if ($finalExamCategories->count() > 1) {
            $issues[] = new GradingConfigViolation(
                'MULTIPLE_FINAL_EXAMS',
                'يوجد أكثر من فئة نهائية واحدة في القالب.',
                ['template_id' => $template->id, 'count' => $finalExamCategories->count()]
            );
        }

        foreach ($template->categories as $category) {
            if ($category->mapping_type === 'final_exam' && ! $category->is_final_exam) {
                $issues[] = new GradingConfigViolation(
                    'MAPPING_FINAL_EXAM_MISMATCH',
                    sprintf('الفئة %s مميزة كـ final_exam في المصدر لكنها ليست علامة نهائية.', $category->name),
                    ['category_id' => $category->id, 'template_id' => $template->id],
                    GradingConfigViolation::SEVERITY_WARNING
                );
            }

            if ($category->is_final_exam && (float) ($category->max_raw_score ?? 0) <= 0) {
                $issues[] = new GradingConfigViolation(
                    'FINAL_EXAM_MAX_SCORE_MISSING',
                    sprintf('الفئة %s نهائية ولكن لا يوجد حد أعلى صحيح للنهائي.', $category->name),
                    ['category_id' => $category->id, 'template_id' => $template->id]
                );
            }

            if (! $category->is_final_exam && $category->weight <= 0) {
                $issues[] = new GradingConfigViolation(
                    'NON_FINAL_ZERO_WEIGHT',
                    sprintf('الفئة %s وزنها صفر أو سالب، وهذا غير مسموح.', $category->name),
                    ['category_id' => $category->id, 'weight' => (float) $category->weight]
                );
            }
        }

        return $issues;
    }
}
