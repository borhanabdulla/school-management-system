<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Adapters;

use App\Domains\Academic\Grading\Actions\Admin\SaveGeneralGradingSettingsAction;
use App\Domains\Academic\Grading\Actions\Admin\SaveGradeScaleAction;
use App\Domains\Academic\Grading\Actions\Admin\SaveGradingTemplateAction;
use App\Domains\Academic\Grading\Actions\Admin\SaveTemplateCategoryAction;
use App\Domains\Academic\Grading\Actions\Admin\DeleteTemplateCategoryAction;
use App\Domains\Academic\Grading\Actions\Admin\SaveSubjectGradingConfigAction;
use App\Domains\Academic\Grading\Actions\Admin\SaveGradebookSettingsAction;
use App\Domains\Academic\Grading\Actions\Admin\SaveMonthlyCategoryMappingsAction;
use App\Domains\Academic\Grading\Data\GeneralSettingsData;
use App\Domains\Academic\Grading\Data\GradeScaleData;
use App\Domains\Academic\Grading\Data\TemplateData;
use App\Domains\Academic\Grading\Data\TemplateCategoryData;
use App\Domains\Academic\Grading\Data\SubjectConfigData;
use App\Domains\Academic\Grading\Data\MonthlyMappingData;
use App\Domains\Academic\Grading\Data\MonthlySettingsData;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Services\GradingSettingsService;

/**
 * GradingActionsAdapter - Adapter pattern للتكامل مع Actions
 * 
 * @responsibility توفير نفس API الخاص بـ GradingSettingsService لكن يستدعي Actions
 * @note هذا Adapter فقط - جميع read operations تُفوّض للـ Service الأصلي
 */
class GradingActionsAdapter
{
    public function __construct(
        private readonly SaveGeneralGradingSettingsAction $saveGeneralSettingsAction,
        private readonly SaveGradeScaleAction $saveGradeScaleAction,
        private readonly SaveGradingTemplateAction $saveTemplateAction,
        private readonly SaveTemplateCategoryAction $saveCategoryAction,
        private readonly DeleteTemplateCategoryAction $deleteCategoryAction,
        private readonly SaveSubjectGradingConfigAction $saveSubjectConfigAction,
        private readonly SaveGradebookSettingsAction $saveGradebookSettingsAction,
        private readonly SaveMonthlyCategoryMappingsAction $saveMappingsAction,
        private readonly GradingSettingsService $settingsService // للـ read operations
    ) {
    }

    // ==========================================
    // Write Operations - تستخدم Actions
    // ==========================================

    /**
     * حفظ الإعدادات العامة
     */
    public function saveGeneralSettings(GeneralSettingsData $data): void
    {
        $this->saveGeneralSettingsAction->execute($data);
    }

    /**
     * حفظ سلم الدرجات
     */
    public function saveGradeScale(GradeScaleData $data): void
    {
        $this->saveGradeScaleAction->execute($data);
    }

    /**
     * حفظ/تحديث قالب تقييم
     */
    public function saveTemplate(?int $templateId, TemplateData $data): GradingTemplate
    {
        return $this->saveTemplateAction->execute($templateId, $data);
    }

    /**
     * حفظ فئة في القالب
     */
    public function saveCategory(TemplateCategoryData $data): TemplateCategory
    {
        return $this->saveCategoryAction->execute($data);
    }

    /**
     * حذف فئة
     */
    public function deleteCategory(int $categoryId): void
    {
        $this->deleteCategoryAction->execute($categoryId);
    }

    /**
     * حفظ تكوين مادة
     */
    public function saveSubjectConfig(SubjectConfigData $data): void
    {
        $this->saveSubjectConfigAction->execute($data);
    }

    /**
     * حفظ إعدادات Gradebook الشهرية
     */
    public function saveGradebookSettings(int $yearId, MonthlySettingsData $settings): void
    {
        $this->saveGradebookSettingsAction->execute($yearId, $settings);
    }

    /**
     * حفظ الإعدادات الشهرية (saveMonthlySettings من Service)
     * @deprecated Use saveGradebookSettings
     */
    public function saveMonthlySettings(\App\Domains\Academic\Grading\Data\MonthlySettingsData $data): void
    {
        // Delegate to Service (هذه write operation تستخدم Service الأصلي)
        $this->settingsService->saveMonthlySettings($data);
    }

    /**
     * حفظ مطابقات الفئات الشهرية
     */
    public function saveMappings(MonthlyMappingData $data, array $mappings, array $calculationKeys): void
    {
        $this->saveMappingsAction->execute($data, $mappings, $calculationKeys);
    }

    // ==========================================
    // Read Operations - تُفوّض للـ Service الأصلي
    // ==========================================

    /**
     * تحميل الإعدادات العامة
     */
    public function loadGeneralSettings(): GeneralSettingsData
    {
        return $this->settingsService->loadGeneralSettings();
    }

    /**
     * تحميل سلم الدرجات
     */
    public function loadGradeScale(): GradeScaleData
    {
        return $this->settingsService->loadGradeScale();
    }

    /**
     * التحقق من صحة سلم الدرجات
     */
    public function validateGradeScale(GradeScaleData $data): array
    {
        return $this->settingsService->validateGradeScale($data);
    }

    /**
     * تحميل قالب تقييم
     */
    public function loadTemplate(int $id): ?GradingTemplate
    {
        return $this->settingsService->loadTemplate($id);
    }

    /**
     * تحميل الإعدادات الشهرية
     */
    public function loadMonthlySettings(): \App\Domains\Academic\Grading\Data\MonthlySettingsData
    {
        return $this->settingsService->loadMonthlySettings();
    }

    /**
     * تحميل تكوينات المواد
     */
    public function loadSubjectConfigs(int $gradeId, int $termId): array
    {
        return $this->settingsService->loadSubjectConfigs($gradeId, $termId);
    }

    /**
     * الحصول على اسم المادة
     */
    public function getSubjectName(int $subjectId): string
    {
        return $this->settingsService->getSubjectName($subjectId);
    }

    /**
     * الحصول على ID السنة الدراسية للترم
     */
    public function getTermAcademicYearId(int $termId): ?int
    {
        return $this->settingsService->getTermAcademicYearId($termId);
    }

    /**
     * إعادة حساب Coursework لمادة
     */
    public function recomputeSubjectCoursework(int $termId, int $gradeId, int $subjectId): bool
    {
        return $this->settingsService->recomputeSubjectCoursework($termId, $gradeId, $subjectId);
    }

    /**
     * الحصول على بيانات العرض
     */
    public function getRenderData(?int $templateTermId, ?int $subjectGradeId, ?int $subjectTermId): \App\Domains\Academic\Grading\Data\GradingSettingsViewData
    {
        return $this->settingsService->getRenderData($templateTermId, $subjectGradeId, $subjectTermId);
    }

    /**
     * الحصول على حالة ربط الدفتر الشهري لكل مادة
     *
     * @return array<int, array{status: string, mapped: int, total: int}>
     */
    public function getMonthlyMappingStatus(?int $gradeId, ?int $termId): array
    {
        return $this->settingsService->getMonthlyMappingStatus($gradeId, $termId);
    }

    /**
     * الحصول على ID الترم النشط
     */
    public function getActiveTermId(): ?int
    {
        return $this->settingsService->getActiveTermId();
    }

    /**
     * الحصول على ID الصف الافتراضي
     */
    public function getDefaultGradeId(): ?int
    {
        return $this->settingsService->getDefaultGradeId();
    }

    /**
     * بناء الحالة الأولية
     */
    public function buildInitialState(): \App\Domains\Academic\Grading\Data\GradingSettingsInitData
    {
        return $this->settingsService->buildInitialState();
    }
}
