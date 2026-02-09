<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Data\TemplateCategoryData;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Services\TemplateCategoryService;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use Illuminate\Support\Facades\Auth;

/**
 * SaveTemplateCategoryAction - حفظ فئة في قالب التقييم
 * 
 * @responsibility إنشاء أو تحديث فئة مع التحقق من صحة الأوزان
 */
class SaveTemplateCategoryAction
{
    public function __construct(
        private readonly TemplateCategoryService $categoryService,
        private readonly GradingLookupService $lookupService
    ) {
    }

    /**
     * تنفيذ Action لحفظ فئة
     * 
     * @param TemplateCategoryData $data بيانات الفئة
     * @return TemplateCategory
     * @throws GradingException
     */
    public function execute(TemplateCategoryData $data): TemplateCategory
    {
        $this->authorize();

        // استخدام Service للـ validation والحفظ
        // Service يتحقق من: weights consistency, parent validity, template limits
        $category = $this->categoryService->saveCategory($data);

        // إبطال cache القوالب
        $this->lookupService->invalidateTemplatesCache();

        return $category;
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
            throw new GradingException('ليس لديك صلاحية إدارة فئات التقييم.');
        }
    }
}
