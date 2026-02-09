<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use Illuminate\Support\Facades\DB;

/**
 * CreateGradingTemplateAction - إنشاء قالب تقييم جديد
 * 
 * ينشئ قالب تقييم مع فئاته بشكل ذري (transactional)
 */
class CreateGradingTemplateAction
{
    /**
     * إنشاء قالب تقييم جديد
     *
     * @param array $templateData بيانات القالب
     * @param array $categories الفئات [{name, weight, ...}, ...]
     * @return GradingTemplate
     */
    public function execute(array $templateData, array $categories = []): GradingTemplate
    {
        return DB::transaction(function () use ($templateData, $categories) {
            // إنشاء القالب
            $template = GradingTemplate::create([
                'name' => $templateData['name'],
                'total_max_score' => $templateData['total_max_score'] ?? 100,
                'pass_score' => $templateData['pass_score'] ?? 50,
                'rounding_rule' => $templateData['rounding_rule'] ?? 'nearest_integer',
                'rounding_precision' => $templateData['rounding_precision'] ?? 0,
                'academic_year_id' => $templateData['academic_year_id'] ?? null,
                'grade_id' => $templateData['grade_id'] ?? null,
                'term_id' => $templateData['term_id'] ?? null,
            ]);

            // إنشاء الفئات
            foreach ($categories as $index => $categoryData) {
                $this->createCategory($template, $categoryData, $index);
            }

            return $template->load('categories');
        });
    }

    /**
     * إنشاء فئة (مع دعم الفئات الفرعية)
     */
    private function createCategory(
        GradingTemplate $template,
        array $data,
        int $order,
        ?int $parentId = null
    ): TemplateCategory {
        $category = TemplateCategory::create([
            'grading_template_id' => $template->id,
            'parent_id' => $parentId,
            'name' => $data['name'],
            'weight' => $data['weight'] ?? 0,
            'max_raw_score' => $data['max_raw_score'] ?? null,
            'calculation_type' => $data['calculation_type'] ?? 'sum',
            'is_dynamic_weight' => $data['is_dynamic_weight'] ?? false,
            'is_locked' => $data['is_locked'] ?? false,
            'pass_required' => $data['pass_required'] ?? false,
            'pass_threshold' => $data['pass_threshold'] ?? null,
            'order' => $order,
        ]);

        // إنشاء الفئات الفرعية إن وجدت
        if (!empty($data['children'])) {
            foreach ($data['children'] as $childIndex => $childData) {
                $this->createCategory($template, $childData, $childIndex, $category->id);
            }
        }

        return $category;
    }
}
