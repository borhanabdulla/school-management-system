<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DeleteTemplateCategoryAction - حذف فئة من قالب التقييم
 * 
 * @responsibility حذف فئة مع التحقق من عدم وجود تبعيات
 * @guard يمنع الحذف إذا كانت الفئة لديها children أو مستخدمة
 */
class DeleteTemplateCategoryAction
{
    public function __construct(
        private readonly GradingLookupService $lookupService
    ) {
    }

    /**
     * تنفيذ Action لحذف فئة
     * 
     * @param int $categoryId معرف الفئة
     * @return void
     * @throws GradingException
     */
    public function execute(int $categoryId): void
    {
        $this->authorize();

        // استخدام transaction لضمان atomicity
        DB::transaction(function () use ($categoryId) {
            $category = TemplateCategory::findOrFail($categoryId);

            $this->guardAgainstChildren($category);
            $this->guardAgainstUsage($category);

            $category->delete();
        });

        // إبطال cache بعد نجاح الحذف
        $this->lookupService->invalidateTemplatesCache();
    }

    /**
     * التحقق من الصلاحيات
     */
    protected function authorize(): void
    {
        if (!Auth::check()) {
            throw new GradingException('يجب تسجيل الدخول أولاً.');
        }

        if (!Auth::user()->can('grading.manage_templates')) {
            throw new GradingException('ليس لديك صلاحية حذف فئات التقييم.');
        }
    }

    /**
     * Guard: منع حذف فئة لديها فئات فرعية
     * 
     * @param TemplateCategory $category
     * @throws GradingException
     */
    protected function guardAgainstChildren(TemplateCategory $category): void
    {
        if ($category->children()->exists()) {
            throw new GradingException(
                'لا يمكن حذف فئة "{$category->name}" لأنها تحتوي على فئات فرعية. ' .
                'يجب حذف الفئات الفرعية أولاً.'
            );
        }
    }

    /**
     * Guard: منع حذف فئة مستخدمة في mappings أو marks
     * 
     * @param TemplateCategory $category
     * @throws GradingException
     */
    protected function guardAgainstUsage(TemplateCategory $category): void
    {
        // التحقق من استخدامها في MonthlyCategoryMapping
        $usedInMappings = MonthlyCategoryMapping::where('template_category_id', $category->id)->exists();

        if ($usedInMappings) {
            throw new GradingException(
                "لا يمكن حذف فئة \"{$category->name}\" لأنها مستخدمة في مطابقات الدرجات الشهرية."
            );
        }

        // التحقق من استخدامها في StudentMark
        $usedInMarks = StudentMark::where('template_category_id', $category->id)->exists();

        if ($usedInMarks) {
            throw new GradingException(
                "لا يمكن حذف فئة \"{$category->name}\" لأنها مستخدمة في درجات الطلاب."
            );
        }
    }
}
