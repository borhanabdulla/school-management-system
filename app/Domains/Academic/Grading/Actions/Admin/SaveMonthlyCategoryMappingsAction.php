<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Data\MonthlyMappingData;
use App\Domains\Academic\Grading\Services\MonthlyMappingService;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use Illuminate\Support\Facades\Auth;

/**
 * SaveMonthlyCategoryMappingsAction - حفظ مطابقات الفئات الشهرية
 * 
 * @responsibility ربط فئات MonthlyGrade بفئات GradingTemplate
 */
class SaveMonthlyCategoryMappingsAction
{
    public function __construct(
        private readonly MonthlyMappingService $mappingService
    ) {
    }

    /**
     * تنفيذ Action لحفظ المطابقات
     * 
     * @param MonthlyMappingData $data بيانات المطابقات
     * @param array $mappings المطابقات [monthlyCategory => templateCategoryId]
     * @param array $calculationKeys مفاتيح الحساب
     * @return void
     * @throws GradingException
     */
    public function execute(MonthlyMappingData $data, array $mappings, array $calculationKeys): void
    {
        $this->authorize();

        // Delegate إلى Service
        $this->mappingService->saveMappings($data, $mappings, $calculationKeys);
    }

    /**
     * التحقق من الصلاحيات
     */
    protected function authorize(): void
    {
        if (!Auth::check()) {
            throw new GradingException('يجب تسجيل الدخول أولاً.');
        }

        if (!Auth::user()->can('grading.manage_settings')) {
            throw new GradingException('ليس لديك صلاحية إدارة مطابقات الفئات.');
        }
    }
}
