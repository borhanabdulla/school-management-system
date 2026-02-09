<?php

namespace App\Livewire\Admin\Grading;

use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Data\TemplateCategoryData;
use App\Domains\Academic\Grading\Data\GeneralSettingsData;
use App\Domains\Academic\Grading\Data\GradeScaleData;
use App\Domains\Academic\Grading\Data\GradingSettingsInitData;
use App\Domains\Academic\Grading\Data\MonthlyMappingData;
use App\Domains\Academic\Grading\Data\MonthlySettingsData;
use App\Domains\Academic\Grading\Data\SubjectConfigData;
use App\Domains\Academic\Grading\Data\TemplateData;
use App\Domains\Academic\Grading\Adapters\GradingActionsAdapter;
use App\Domains\Academic\Grading\Services\MonthlyMappingService;
use App\Domains\Academic\Grading\Services\TemplateCategoryService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class GradingSettings extends Component
{
    // Wizard steps: general, scale, templates, subjects, monthly
    public string $activeTab = 'general';
    public bool $showStepHelp = true;
    public array $wizardSteps = [
        'general' => [
            'label' => 'الإعدادات العامة',
            'title' => 'الأساسيات أولاً',
            'description' => 'تحديد قواعد النجاح والرأفة وأوزان الفصول يضبط حساب النتائج في كل النظام.',
            'effects' => [
                'تؤثر على نجاح/رسوب الطالب تلقائياً.',
                'تحدد توزيع نتائج الفصول على الدرجة النهائية.',
            ],
        ],
        'scale' => [
            'label' => 'سلم التقديرات',
            'title' => 'تعريف التقديرات الرسمية',
            'description' => 'سلم التقدير هو المرجع الوحيد لتحويل النسبة المئوية إلى تقدير.',
            'effects' => [
                'يُستخدم في نتائج الترم والنتائج النهائية والتقارير.',
                'أي تغيير هنا ينعكس على كل التقارير.',
            ],
        ],
        'templates' => [
            'label' => 'القوالب والهيكلة',
            'title' => 'بناء هيكل الدرجات',
            'description' => 'أنشئ قالباً يحدد الفئات (أعمال سنة/نهائي) وأوزانها.',
            'effects' => [
                'القالب هو المصدر لحساب الدرجات وتجميعها.',
                'الفئات غير المضبوطة تسبب Missing/Invalid في الصحة.',
            ],
        ],
        'subjects' => [
            'label' => 'تهيئة المواد',
            'title' => 'ربط المادة بالقالب الصحيح',
            'description' => 'لكل مادة يجب اختيار القالب المناسب وربط المابينغ الشهري بعد تجهيز الدفتر الشهري.',
            'effects' => [
                'يحدد كيفية احتساب الدرجة للمادة.',
                'بدون هذا الربط لا يمكن المعالجة/النشر.',
            ],
        ],
        'monthly' => [
            'label' => 'الدفتر الشهري',
            'title' => 'تجهيز بنود الدفتر الشهري',
            'description' => 'حدد بنود الشهر وقواعد خصم المواظبة، ثم اربط البنود بفئات التقييم لضمان تجميع صحيح.',
            'effects' => [
                'يعكس أعمدة دفتر المعلم الشهرية.',
                'يؤثر على تجميع أعمال السنة.',
                'الربط ضروري قبل أي تجميع أو نشر.',
            ],
        ],
    ];

    // --- General Settings ---
    public int $defaultPassScore = 50;
    public int $graceMarksLimit = 2;
    public array $termWeights = [];

    // --- Grade Scale ---
    public array $gradeScale = [];

    // --- Templates ---
    #[Locked]
    public ?int $activeTemplateId = null;
    public ?GradingTemplate $activeTemplate = null;
    public string $templateName = '';

    #[Locked]
    public ?int $templateAcademicYearId = null;

    #[Locked]
    public ?int $templateGradeId = null;

    #[Locked]
    public ?int $templateTermId = null;

    // --- Template Tree Builder ---
    public bool $showCategoryForm = false;

    #[Locked]
    public int $editingCategoryId = 0;

    #[Locked]
    public ?int $parentCategoryId = null;

    public string $categoryName = '';
    public float $categoryWeight = 0;
    public float $categoryMaxRawScore = 0;
    public string $categoryCalculationType = 'sum';
    public bool $categoryIsDynamic = false;
    public bool $categoryIsLocked = false;
    public string $categoryMappingType = 'manual';
    public bool $categoryPassRequired = false;
    public float $categoryPassThreshold = 0;
    public bool $categoryIsReadonly = false;
    public bool $categoryIsFinalExam = false;

    // --- Subject Configuration ---
    #[Locked]
    public ?int $subjectGradeId = null;

    #[Locked]
    public ?int $subjectTermId = null;
    public array $subjectConfigs = []; // [subject_id => template_id]

    public bool $gradingHealthIsClean = true;
    public int $gradingHealthMissingCount = 0;
    public int $gradingHealthInvalidCount = 0;


    protected $listeners = [
        'gradingHealthReportUpdated' => 'onGradingHealthReportUpdated',
    ];

    // Monthly Gradebook Logic
    // ==========================================
    public array $monthlyCategories = [];
    public int $attendanceDeductAfter = 3;
    public float $attendanceDeductPerAbsence = 0.5;
    public float $attendanceMaxScore = 5;
    public bool $allowCustomCategories = true;

    // Monthly Mapping
    public bool $showMonthlyMappingModal = false;

    #[Locked]
    public ?int $mappingSubjectId = null;
    public string $mappingSubjectName = '';
    public array $mappingCategories = [];
    public array $mappingTemplateCategories = [];
    public array $monthlyCategoryMappings = [];
    public array $aggregationRuleOptions = [
        'sum' => 'جمع',
        'avg' => 'متوسط',
        'last' => 'آخر قيمة',
        'weighted' => 'موزون',
    ];
    public array $missingMonthsPolicyOptions = [
        'ignore' => 'تجاهل',
        'zero' => 'اعتبارها صفر',
    ];

    #[Computed]
    public function gradingQueueLastHeartbeat(): ?string
    {
        return Cache::get('grading.queue.last_heartbeat_at');
    }

    #[Computed]
    public function gradingQueueIsStale(): bool
    {
        $last = $this->gradingQueueLastHeartbeat();
        if (! $last) {
            return true;
        }

        try {
            $lastAt = Carbon::parse($last);
        } catch (\Throwable) {
            return true;
        }

        return $lastAt->lt(now()->subMinutes(15));
    }


    public function mount()
    {
        $state = $this->settingsService()->buildInitialState();
        $this->applyInitialState($state);

        if (!$this->subjectTermId) {
            $this->dispatch('error', message: 'يجب تفعيل ترم قبل إعدادات الدرجات.');
        }
        $this->templateTermId = $this->subjectTermId;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->showStepHelp = true;
    }

    public function dismissStepHelp(): void
    {
        $this->showStepHelp = false;
    }

    // ==========================================
    // General Settings Logic
    // ==========================================
    public function loadGeneralSettings()
    {
        $this->applyGeneralSettings($this->settingsService()->loadGeneralSettings());
    }

    public function saveGeneralSettings()
    {
        $this->validate([
            'defaultPassScore' => 'required|integer|min:0|max:100',
            'graceMarksLimit' => 'required|integer|min:0|max:10',
            'termWeights.*' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $this->settingsService()->saveGeneralSettings(new GeneralSettingsData(
                defaultPassScore: $this->defaultPassScore,
                graceMarksLimit: $this->graceMarksLimit,
                termWeights: $this->termWeights
            ));
        } catch (\InvalidArgumentException $e) {
            $this->addError('termWeights', $e->getMessage());
            return;
        }

        $this->dispatch('notify', message: 'تم حفظ الإعدادات العامة بنجاح', type: 'success');
    }

    // ==========================================
    // Grade Scale Logic
    // ==========================================
    public function loadGradeScale()
    {
        $this->applyGradeScale($this->settingsService()->loadGradeScale());
    }

    public function saveGradeScale()
    {
        $this->resetErrorBag('gradeScale');
        $issues = $this->settingsService()->validateGradeScale(new GradeScaleData(
            scale: $this->gradeScale
        ));

        if ($issues !== []) {
            foreach ($issues as $issue) {
                $this->addError('gradeScale', $issue);
            }
            return;
        }

        $this->settingsService()->saveGradeScale(new GradeScaleData(
            scale: $this->gradeScale
        ));
        $this->dispatch('notify', message: 'تم حفظ سلم التقديرات بنجاح', type: 'success');
    }

    // ==========================================
    // Templates Logic
    // ==========================================
    public function selectTemplate($id)
    {
        $this->activeTemplateId = $id;
        $this->activeTemplate = $this->settingsService()->loadTemplate($id);

        if ($this->activeTemplate) {
            $this->templateName = $this->activeTemplate->name;
            $this->templateAcademicYearId = $this->activeTemplate->academic_year_id;
            $this->templateGradeId = $this->activeTemplate->grade_id;
            $this->templateTermId = $this->activeTemplate->term_id ?? $this->subjectTermId;
        }
    }

    public function createTemplate()
    {
        $this->activeTemplateId = null;
        $this->activeTemplate = null;
        $this->templateName = '';
        $this->templateAcademicYearId = null;
        $this->templateGradeId = null;
        $this->templateTermId = $this->subjectTermId;
    }

    public function saveTemplate()
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'templateTermId' => 'required|exists:terms,id',
        ]);

        if ($this->activeTemplateId && !$this->validateTemplateIntegrity($this->activeTemplateId)) {
            return;
        }

        $template = $this->settingsService()->saveTemplate(
            $this->activeTemplateId,
            new TemplateData(
                name: $this->templateName,
                academicYearId: $this->templateAcademicYearId,
                gradeId: $this->templateGradeId,
                termId: (int) $this->templateTermId
            )
        );

        $this->selectTemplate($template->id);
        $this->dispatch('notify', message: 'تم حفظ القالب بنجاح', type: 'success');
    }

    // ==========================================
    // Tree Builder Logic
    // ==========================================
    public function openCategoryForm(?int $parentId = null, ?int $categoryId = null)
    {
        $this->showCategoryForm = true;
        $this->parentCategoryId = $parentId;
        $this->editingCategoryId = $categoryId ?? 0;

        if ($categoryId) {
            $category = $this->templateCategoryService()->getCategoryData($categoryId);
            if (!$category) {
                $this->resetCategoryForm();
                return;
            }

            $this->categoryName = $category->name;
            $this->categoryWeight = $category->weight;
            $this->categoryMaxRawScore = $category->maxRawScore;
            $this->categoryCalculationType = $category->calculationType;
            $this->categoryIsDynamic = $category->isDynamicWeight;
            $this->categoryIsLocked = $category->isLocked;
            $this->categoryMappingType = $category->mappingType;
            $this->categoryPassRequired = $category->passRequired;
            $this->categoryPassThreshold = $category->passThreshold;
            $this->categoryIsReadonly = $category->isReadonly;
            $this->categoryIsFinalExam = $category->isFinalExam;
        } else {
            $this->resetCategoryForm();
        }
    }

    public function resetCategoryForm()
    {
        $this->categoryName = '';
        $this->categoryWeight = 0;
        $this->categoryMaxRawScore = 0;
        $this->categoryCalculationType = 'sum';
        $this->categoryIsDynamic = false;
        $this->categoryIsLocked = false;
        $this->categoryMappingType = 'manual';
        $this->categoryPassRequired = false;
        $this->categoryPassThreshold = 0;
        $this->categoryIsReadonly = false;
        $this->categoryIsFinalExam = false;
    }

    public function saveCategory()
    {
        $this->validate([
            'categoryName' => 'required|string',
            'categoryWeight' => 'required|numeric|min:0.01',
        ]);

        if (!$this->activeTemplateId) {
            $this->addError('categoryName', 'يجب اختيار قالب قبل إضافة الفئات.');
            return;
        }

        if ($this->categoryPassRequired) {
            $this->validate([
                'categoryPassThreshold' => 'required|numeric|min:0|max:100',
            ]);
        }

        $payload = new TemplateCategoryData(
            id: $this->editingCategoryId ?: null,
            templateId: $this->activeTemplateId,
            parentId: $this->parentCategoryId,
            name: $this->categoryName,
            weight: (float) $this->categoryWeight,
            maxRawScore: (float) $this->categoryMaxRawScore,
            calculationType: $this->categoryCalculationType,
            isDynamicWeight: $this->categoryIsDynamic,
            isLocked: $this->categoryIsLocked,
            mappingType: $this->categoryMappingType,
            passRequired: $this->categoryPassRequired,
            passThreshold: (float) $this->categoryPassThreshold,
            isReadonly: $this->categoryIsReadonly,
            isFinalExam: $this->categoryIsFinalExam
        );

        $issues = $this->templateCategoryService()->validateCategoryRules($payload);
        if ($issues !== []) {
            foreach ($issues as $field => $message) {
                $this->addError($field, $message);
            }

            return;
        }

        $this->templateCategoryService()->saveCategory($payload);

        $this->showCategoryForm = false;
        $this->selectTemplate($this->activeTemplateId); // Reload tree
    }

    public function deleteCategory($id)
    {
        $this->templateCategoryService()->deleteCategory($id);
        $this->selectTemplate($this->activeTemplateId);
    }

    private function validateTemplateIntegrity(int $templateId): bool
    {
        $issues = $this->templateCategoryService()->validateTemplateIntegrity($templateId);
        if ($issues !== []) {
            foreach ($issues as $issue) {
                $this->addError('templateName', $issue);
            }

            return false;
        }

        return true;
    }

    // ==========================================
    // Subject Configuration Logic
    // ==========================================
    public function loadSubjectConfigs()
    {
        if (!$this->subjectGradeId || !$this->subjectTermId)
            return;

        $this->subjectConfigs = $this->settingsService()->loadSubjectConfigs(
            $this->subjectGradeId,
            $this->subjectTermId
        );
    }

    public function updatedSubjectGradeId()
    {
        $this->loadSubjectConfigs();
    }
    public function updatedSubjectTermId()
    {
        $this->loadSubjectConfigs();
        $this->templateTermId = $this->subjectTermId;
        $this->activeTemplateId = null;
        $this->activeTemplate = null;
    }

    public function saveSubjectConfig($subjectId)
    {
        if (!$this->subjectTermId) {
            $this->dispatch('notify', message: 'يرجى اختيار الفصل الدراسي أولاً', type: 'error');
            return;
        }

        $templateId = $this->subjectConfigs[$subjectId] ?? null;

        try {
            $this->settingsService()->saveSubjectConfig(new SubjectConfigData(
                subjectId: $subjectId,
                gradeId: (int) $this->subjectGradeId,
                termId: (int) $this->subjectTermId,
                templateId: (int) $templateId
            ));

            $this->dispatch('notify', message: 'تم حفظ إعدادات المادة بنجاح', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'حدث خطأ أثناء الحفظ: ' . $e->getMessage(), type: 'error');
        }
    }

    // ==========================================

    public function loadMonthlySettings()
    {
        $this->applyMonthlySettings($this->settingsService()->loadMonthlySettings());
    }

    public function addMonthlyCategory()
    {
        $this->monthlyCategories[] = [
            'key' => '',
            'label' => '',
            'max_score' => 10,
            'is_default' => true,
            'is_attendance' => false,
        ];
    }

    public function removeMonthlyCategory($index)
    {
        unset($this->monthlyCategories[$index]);
        $this->monthlyCategories = array_values($this->monthlyCategories);
    }

    public function saveMonthlySettings()
    {
        $this->validate([
            'monthlyCategories.*.label' => 'required|string',
            'monthlyCategories.*.max_score' => 'required|numeric|min:1',
            'attendanceDeductAfter' => 'required|integer|min:0',
            'attendanceDeductPerAbsence' => 'required|numeric|min:0',
        ]);

        $this->settingsService()->saveMonthlySettings(new MonthlySettingsData(
            categories: $this->monthlyCategories,
            attendanceDeductAfter: $this->attendanceDeductAfter,
            attendanceDeductPerAbsence: $this->attendanceDeductPerAbsence,
            attendanceMaxScore: $this->attendanceMaxScore,
            allowCustomCategories: $this->allowCustomCategories
        ));

        $this->dispatch('notify', message: 'تم حفظ إعدادات الدفتر الشهري بنجاح', type: 'success');
    }

    public function openMonthlyMapping(int $subjectId): void
    {
        if (!$this->subjectTermId || !$this->subjectGradeId) {
            $this->dispatch('notify', message: 'اختر الصف والترم قبل إعداد المابينغ.', type: 'error');
            return;
        }

        $this->mappingSubjectId = $subjectId;
        $this->mappingSubjectName = $this->settingsService()->getSubjectName($subjectId);
        $this->showMonthlyMappingModal = true;
        $this->loadMonthlyMappings();
    }

    public function closeMonthlyMapping(): void
    {
        $this->showMonthlyMappingModal = false;
        $this->mappingSubjectId = null;
        $this->mappingSubjectName = '';
        $this->mappingCategories = [];
        $this->mappingTemplateCategories = [];
        $this->monthlyCategoryMappings = [];
    }

    public function saveMonthlyMappings(): void
    {
        if (!$this->mappingSubjectId || !$this->subjectTermId || !$this->subjectGradeId) {
            $this->dispatch('notify', message: 'البيانات غير مكتملة لحفظ المابينغ.', type: 'error');
            return;
        }

        $academicYearId = $this->settingsService()->getTermAcademicYearId($this->subjectTermId);
        if (!$academicYearId) {
            $this->dispatch('notify', message: 'الترم غير موجود.', type: 'error');
            return;
        }

        try {
            $this->monthlyMappingService()->saveMappings(
                new MonthlyMappingData(
                    academicYearId: $academicYearId,
                    termId: (int) $this->subjectTermId,
                    gradeId: (int) $this->subjectGradeId,
                    subjectId: (int) $this->mappingSubjectId,
                    mappings: $this->monthlyCategoryMappings
                ),
                $this->aggregationRuleOptions,
                $this->missingMonthsPolicyOptions
            );

            $this->dispatch('notify', message: 'تم حفظ المابينغ بنجاح', type: 'success');
            $this->loadMonthlyMappings();
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: 'حدث خطأ أثناء حفظ المابينغ.', type: 'error');
        }
    }

    public function recomputeSubjectCoursework(int $subjectId): void
    {
        if (!$this->subjectTermId || !$this->subjectGradeId) {
            $this->dispatch('notify', message: 'اختر الصف والترم قبل إعادة التجميع.', type: 'error');
            return;
        }

        $success = $this->settingsService()->recomputeSubjectCoursework(
            (int) $this->subjectTermId,
            (int) $this->subjectGradeId,
            $subjectId
        );
        if (!$success) {
            $this->dispatch('notify', message: 'الترم غير موجود.', type: 'error');
            return;
        }

        $this->dispatch('notify', message: 'تمت إعادة التجميع للمادة.', type: 'success');
    }

    public function onGradingHealthReportUpdated(array $payload): void
    {
        $this->gradingHealthIsClean = (bool) ($payload['clean'] ?? true);
        $this->gradingHealthMissingCount = $payload['missing'] ?? 0;
        $this->gradingHealthInvalidCount = $payload['invalid'] ?? 0;
    }

    private function loadMonthlyMappings(): void
    {
        $this->mappingCategories = [];
        $this->mappingTemplateCategories = [];
        $this->monthlyCategoryMappings = [];

        if (!$this->mappingSubjectId || !$this->subjectTermId || !$this->subjectGradeId) {
            return;
        }

        $payload = $this->monthlyMappingService()->loadMappings(
            $this->mappingSubjectId,
            $this->subjectGradeId,
            $this->subjectTermId
        );

        $this->mappingCategories = $payload['categories'];
        $this->mappingTemplateCategories = $payload['templateCategories'];
        $this->monthlyCategoryMappings = $payload['mappings'];
    }

    public function render()
    {
        $data = $this->settingsService()->getRenderData(
            $this->templateTermId,
            $this->subjectGradeId,
            $this->subjectTermId
        )->toArray();

        $data['gradingQueueIsStale'] = $this->gradingQueueIsStale();
        $data['gradingQueueLastHeartbeat'] = $this->gradingQueueLastHeartbeat();

        return view(
            'livewire.admin.grading.grading-settings',
            $data
        );
    }

    private function settingsService(): GradingActionsAdapter
    {
        return app(GradingActionsAdapter::class);
    }

    private function monthlyMappingService(): MonthlyMappingService
    {
        return app(MonthlyMappingService::class);
    }

    private function templateCategoryService(): TemplateCategoryService
    {
        return app(TemplateCategoryService::class);
    }

    private function applyInitialState(GradingSettingsInitData $state): void
    {
        $this->applyGeneralSettings($state->generalSettings);
        $this->applyGradeScale($state->gradeScale);
        $this->applyMonthlySettings($state->monthlySettings);

        $this->subjectGradeId = $state->defaultGradeId;
        $this->subjectTermId = $state->activeTermId;
        $this->subjectConfigs = $state->subjectConfigs;

        if ($state->firstTemplateId) {
            $this->selectTemplate($state->firstTemplateId);
        }
    }

    private function applyGeneralSettings(GeneralSettingsData $settings): void
    {
        $this->defaultPassScore = $settings->defaultPassScore;
        $this->graceMarksLimit = $settings->graceMarksLimit;
        $this->termWeights = $settings->termWeights;
    }

    private function applyGradeScale(GradeScaleData $settings): void
    {
        $this->gradeScale = $settings->scale;
    }

    private function applyMonthlySettings(?MonthlySettingsData $settings): void
    {
        if (!$settings) {
            return;
        }

        $this->monthlyCategories = $settings->categories;
        $this->attendanceDeductAfter = $settings->attendanceDeductAfter;
        $this->attendanceDeductPerAbsence = $settings->attendanceDeductPerAbsence;
        $this->attendanceMaxScore = $settings->attendanceMaxScore;
        $this->allowCustomCategories = $settings->allowCustomCategories;
    }
}
