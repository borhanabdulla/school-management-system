<?php

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\Grading\Data\TemplateCategoryData;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\TemplateCategory;

class TemplateCategoryService
{
    public function getCategoryData(int $categoryId): ?TemplateCategoryData
    {
        $category = TemplateCategory::find($categoryId);
        if (! $category) {
            return null;
        }

        return TemplateCategoryData::fromModel($category);
    }

    public function saveCategory(TemplateCategoryData $data): TemplateCategory
    {
        return TemplateCategory::updateOrCreate(
            ['id' => $data->id ?: null],
            [
                'grading_template_id' => $data->templateId,
                'parent_id' => $data->parentId,
                'name' => $data->name,
                'weight' => $data->weight,
                'max_raw_score' => $data->maxRawScore > 0 ? $data->maxRawScore : null,
                'calculation_type' => $data->calculationType,
                'is_dynamic_weight' => $data->isDynamicWeight,
                'is_locked' => $data->isLocked,
                'mapping_type' => $data->mappingType,
                'pass_required' => $data->passRequired,
                'pass_threshold' => $data->passRequired ? $data->passThreshold : null,
                'is_readonly' => $data->isReadonly,
                'is_final_exam' => $data->isFinalExam,
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    public function validateCategoryRules(TemplateCategoryData $data): array
    {
        $errors = [];

        if ($data->mappingType === 'final_exam' && ! $data->isFinalExam) {
            $errors['categoryIsFinalExam'] = 'لا يمكن تعيين mapping نهائي لفئة غير نهائية.';
        }

        if ($data->isFinalExam && $this->hasAnotherFinalExamCategory($data->templateId, $data->id)) {
            $errors['categoryIsFinalExam'] = 'يوجد فئة نهائية أخرى بالفعل في هذا القالب.';
        }

        if ($data->isFinalExam && $data->maxRawScore <= 0) {
            $errors['categoryMaxRawScore'] = 'الحد الأعلى للفئة النهائية مطلوب ويجب أن يكون أكبر من صفر.';
        }

        return $errors;
    }

    public function deleteCategory(int $categoryId): void
    {
        TemplateCategory::destroy($categoryId);
    }

    public function hasAnotherFinalExamCategory(int $templateId, ?int $excludeId): bool
    {
        return TemplateCategory::where('grading_template_id', $templateId)
            ->where('is_final_exam', true)
            ->when(
                $excludeId,
                fn ($query) => $query->where('id', '!=', $excludeId)
            )
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    public function validateTemplateIntegrity(int $templateId): array
    {
        $categories = TemplateCategory::where('grading_template_id', $templateId)->get();

        $issues = [];
        if ($categories->isEmpty()) {
            $isUsed = SubjectGradingConfig::where('grading_template_id', $templateId)->exists();
            if ($isUsed) {
                $issues[] = 'القالب لا يحتوي على فئات.';
            }

            return $issues;
        }

        $rootCategories = $categories->whereNull('parent_id');

        if ($rootCategories->isEmpty()) {
            $issues[] = 'القالب يجب أن يحتوي على فئات جذرية.';
        }

        $weightSum = (float) $rootCategories->sum('weight');
        if (abs($weightSum - 100.0) > 0.01) {
            $issues[] = 'مجموع أوزان الفئات الجذرية يجب أن يساوي 100%.';
        }

        $finalExamCount = $categories->where('is_final_exam', true)->count();
        if ($finalExamCount > 1) {
            $issues[] = 'لا يمكن حفظ القالب وفيه أكثر من فئة نهائية واحدة.';
        }

        $missingThresholds = $categories->where('pass_required', true)
            ->filter(fn ($category) => $category->pass_threshold === null);
        if ($missingThresholds->isNotEmpty()) {
            $issues[] = 'يوجد فئات تتطلب pass_threshold لكنها فارغة.';
        }

        foreach ($categories as $category) {
            if ($category->is_final_exam && (float) ($category->max_raw_score ?? 0) <= 0) {
                $issues[] = 'الفئة النهائية يجب أن تحتوي على حد أعلى صحيح للدرجة.';
            }

            if (! $category->is_final_exam && $category->weight <= 0) {
                $issues[] = 'يوجد فئات وزنها صفر أو سالب، وهذا غير مسموح.';
            }
        }

        return $issues;
    }
}
