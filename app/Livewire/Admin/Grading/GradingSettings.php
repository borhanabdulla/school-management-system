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
use App\Domains\Academic\Grading\Actions\ApplyTemplateToGradeAction;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Services\MonthlyMappingService;
use App\Domains\Academic\Grading\Services\TemplateCategoryService;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class GradingSettings extends Component
{
    // Wizard steps: templates, subjects, monthly, general, scale, review
    public string $activeTab = 'templates';
    public bool $showStepHelp = true;
    public array $wizardSteps = [
        'general' => [
            'label' => 'الإعدادات العامة',
            'title' => 'الأساسيات أولاً',
            'description' => 'تحديد قواعد النجاح والرأفة وأوزان الفصول يضبط حساب النتائج في كل النظام.',
            'requirements' => [
                'اضبط درجة النجاح وحد الرأفة.',
                'وزّع أوزان الفصول حتى يصبح المجموع 100%.',
            ],
            'effects' => [
                'تؤثر على نجاح/رسوب الطالب تلقائياً.',
                'تحدد توزيع نتائج الفصول على الدرجة النهائية.',
            ],
        ],
        'scale' => [
            'label' => 'سلم التقديرات',
            'title' => 'تعريف التقديرات الرسمية',
            'description' => 'سلم التقدير هو المرجع الوحيد لتحويل النسبة المئوية إلى تقدير.',
            'requirements' => [
                'حدد درجات التقدير ونطاق كل درجة.',
            ],
            'effects' => [
                'يُستخدم في نتائج الترم والنتائج النهائية والتقارير.',
                'أي تغيير هنا ينعكس على كل التقارير.',
            ],
        ],
        'templates' => [
            'label' => 'القوالب والهيكلة',
            'title' => 'بناء هيكل الدرجات',
            'description' => 'أنشئ القالب وحدد الترم الذي سيطبق عليه.',
            'requirements' => [
                'أنشئ قالبًا واحفظه مع الترم.',
            ],
            'effects' => [
                'القالب هو المصدر لحساب الدرجات وتجميعها.',
                'الفئات غير المضبوطة تسبب Missing/Invalid في الصحة.',
            ],
        ],
        'categories' => [
            'label' => 'الفئات',
            'title' => 'تعريف فئات القالب',
            'description' => 'أضف الفئات الجذرية وفرعها وحدد أوزانها.',
            'requirements' => [
                'أضف فئات جذرية واحدة على الأقل.',
                'اجعل مجموع أوزان الفئات الجذرية = 100%.',
            ],
            'effects' => [
                'أي خلل هنا يظهر كـ Invalid في الفحص.',
            ],
        ],
        'subjects' => [
            'label' => 'تهيئة المواد',
            'title' => 'ربط المادة بالقالب الصحيح',
            'description' => 'لكل مادة يجب اختيار القالب المناسب وربط المابينغ الشهري بعد تجهيز الدفتر الشهري.',
            'requirements' => [
                'اختر الصف والترم.',
                'اربط كل مادة بقالب مناسب.',
            ],
            'effects' => [
                'يحدد كيفية احتساب الدرجة للمادة.',
                'بدون هذا الربط لا يمكن المعالجة/النشر.',
            ],
        ],
        'monthly' => [
            'label' => 'الدفتر الشهري',
            'title' => 'تجهيز بنود الدفتر الشهري',
            'description' => 'حدد بنود الشهر وقواعد خصم المواظبة، ثم اربط البنود بفئات التقييم لضمان تجميع صحيح.',
            'requirements' => [
                'عرّف البنود الشهرية واحفظها.',
                'اربط البنود بفئات القالب لكل مادة (مابينغ).',
            ],
            'effects' => [
                'يعكس أعمدة دفتر المعلم الشهرية.',
                'يؤثر على تجميع أعمال السنة.',
                'الربط ضروري قبل أي تجميع أو نشر.',
            ],
        ],
        'review' => [
            'label' => 'المراجعة النهائية',
            'title' => 'تأكيد الجاهزية',
            'description' => 'مراجعة صحة الإعدادات قبل الإغلاق أو النشر.',
            'requirements' => [
                'شغّل فحص الصحة وتأكد من عدم وجود Invalid/Missing.',
            ],
            'effects' => [
                'تمنع الأخطاء قبل الإغلاق.',
                'توضح أسباب المنع إن وُجدت.',
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
    public bool $applyTemplateToAllTerms = false;
    public bool $applyTemplateToSubjects = false;

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
    /** @var array<int, int|string> */
    public array $subjectConfigs = []; // [subject_id => template_id]
    public string $subjectSearch = '';
    public ?int $bulkTemplateId = null;

    public bool $gradingHealthIsClean = true;
    public int $gradingHealthMissingCount = 0;
    public int $gradingHealthInvalidCount = 0;
    public bool $gradingHealthChecked = false;


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
        $tab = (string) $tab;
        $order = $this->stepOrder();

        if (! in_array($tab, $order, true)) {
            return;
        }

        $currentIndex = array_search($this->activeTab, $order, true);
        $targetIndex = array_search($tab, $order, true);
        if ($targetIndex === false) {
            return;
        }

        if ($currentIndex !== false && $targetIndex <= $currentIndex) {
            $this->activeTab = $tab;
            $this->showStepHelp = true;
            return;
        }

        $this->resetErrorBag();
        for ($i = 0; $i < $targetIndex; $i++) {
            $step = $order[$i];
            if (! $this->isStepComplete($step)) {
                $this->activeTab = $step;
                $this->showStepHelp = true;
                $this->dispatch('notify', message: 'يرجى إكمال الخطوة الحالية قبل الانتقال.', type: 'error');
                return;
            }
        }

        $this->activeTab = $tab;
        $this->showStepHelp = true;
    }

    public function dismissStepHelp(): void
    {
        $this->showStepHelp = false;
    }

    public function goNextStep(): void
    {
        $order = $this->stepOrder();
        $currentIndex = array_search($this->activeTab, $order, true);
        if ($currentIndex === false) {
            return;
        }

        $nextStep = $order[$currentIndex + 1] ?? null;
        if (! $nextStep) {
            return;
        }

        $this->setTab($nextStep);
    }

    public function goPreviousStep(): void
    {
        $order = $this->stepOrder();
        $currentIndex = array_search($this->activeTab, $order, true);
        if ($currentIndex === false) {
            return;
        }

        $previousStep = $order[$currentIndex - 1] ?? null;
        if (! $previousStep) {
            return;
        }

        $this->setTab($previousStep);
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
        $this->requestHealthRefresh();
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
        $this->requestHealthRefresh();
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

        if ($this->activeTemplateId) {
            $hasCategories = TemplateCategory::where('grading_template_id', $this->activeTemplateId)->exists();
            if ($hasCategories && ! $this->validateTemplateIntegrity($this->activeTemplateId)) {
                return;
            }
        }

        $academicYearId = $this->templateAcademicYearId;
        if ($this->templateTermId && ! $academicYearId) {
            $term = Term::find($this->templateTermId);
            if ($term) {
                $academicYearId = $term->academic_year_id;
                $this->templateAcademicYearId = $academicYearId;
            }
        }

        try {
            $template = $this->settingsService()->saveTemplate(
                $this->activeTemplateId,
                new TemplateData(
                    name: $this->templateName,
                    academicYearId: $academicYearId,
                    gradeId: $this->templateGradeId,
                    termId: (int) $this->templateTermId
                )
            );
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        }

        $this->selectTemplate($template->id);

        if ($this->applyTemplateToAllTerms) {
            $this->applyTemplateBaselineToTerms($template);
        }

        if ($this->applyTemplateToSubjects) {
            $this->applyTemplateToSubjectsForTerm($template, $template->term_id);
        }

        $this->applyTemplateToAllTerms = false;
        $this->applyTemplateToSubjects = false;

        $this->dispatch('notify', message: 'تم حفظ القالب بنجاح', type: 'success');
        $this->requestHealthRefresh();
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

    public function resetCategoryDefaults(): void
    {
        $this->categoryCalculationType = 'sum';
        $this->categoryMappingType = 'manual';
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
        $this->requestHealthRefresh();
    }

    public function deleteCategory($id)
    {
        $this->templateCategoryService()->deleteCategory($id);
        $this->selectTemplate($this->activeTemplateId);
        $this->requestHealthRefresh();
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

    public function updatedTemplateTermId(): void
    {
        if (! $this->templateTermId) {
            return;
        }

        $term = Term::find($this->templateTermId);
        if ($term) {
            $this->templateAcademicYearId = $term->academic_year_id;
        }
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
            $this->requestHealthRefresh();
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'حدث خطأ أثناء الحفظ: ' . $e->getMessage(), type: 'error');
        }
    }

    public function applyTemplateToAllSubjects(): void
    {
        if (!$this->subjectGradeId || !$this->subjectTermId) {
            $this->dispatch('notify', message: 'يرجى اختيار الصف والترم أولاً', type: 'error');
            return;
        }

        if (!$this->bulkTemplateId) {
            $this->dispatch('notify', message: 'اختر قالبًا لتطبيقه على المواد', type: 'error');
            return;
        }

        $subjects = \App\Domains\Academic\Grade\Models\Grade::find($this->subjectGradeId)?->subjects ?? collect();
        if ($subjects->isEmpty()) {
            $this->dispatch('notify', message: 'لا توجد مواد لتطبيق القالب', type: 'error');
            return;
        }

        try {
            foreach ($subjects as $subject) {
                $this->subjectConfigs[$subject->id] = $this->bulkTemplateId;
                $this->settingsService()->saveSubjectConfig(new SubjectConfigData(
                    subjectId: $subject->id,
                    gradeId: (int) $this->subjectGradeId,
                    termId: (int) $this->subjectTermId,
                    templateId: (int) $this->bulkTemplateId
                ));
            }

            $this->dispatch('notify', message: 'تم تطبيق القالب على جميع المواد', type: 'success');
            $this->requestHealthRefresh();
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: 'تعذر تطبيق القالب على جميع المواد', type: 'error');
        }
    }

    public function saveAllSubjectConfigs(): void
    {
        if (! $this->subjectGradeId || ! $this->subjectTermId) {
            $this->dispatch('notify', message: 'يرجى اختيار الصف والترم أولاً', type: 'error');
            return;
        }

        $subjects = Grade::find($this->subjectGradeId)?->subjects ?? collect();
        if ($subjects->isEmpty()) {
            $this->dispatch('notify', message: 'لا توجد مواد للحفظ', type: 'warning');
            return;
        }

        $missing = $subjects->filter(fn ($subject) => empty($this->subjectConfigs[$subject->id] ?? null))->count();
        if ($missing > 0) {
            $this->addError('subjectConfigs', 'اربط جميع المواد بقالب مناسب قبل الحفظ.');
            return;
        }

        try {
            foreach ($subjects as $subject) {
                $templateId = $this->subjectConfigs[$subject->id] ?? null;
                $this->settingsService()->saveSubjectConfig(new SubjectConfigData(
                    subjectId: $subject->id,
                    gradeId: (int) $this->subjectGradeId,
                    termId: (int) $this->subjectTermId,
                    templateId: (int) $templateId
                ));
            }

            $this->dispatch('notify', message: 'تم حفظ ربط المواد بنجاح', type: 'success');
            $this->requestHealthRefresh();
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: 'حدث خطأ أثناء حفظ ربط المواد', type: 'error');
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
        $this->requestHealthRefresh();
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
            $this->requestHealthRefresh();
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
        $this->gradingHealthChecked = true;
    }

    private function requestHealthRefresh(): void
    {
        $this->dispatch('runGradingHealthCheckRequested');
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

    /**
     * @return array<int, string>
     */
    private function stepOrder(): array
    {
        return ['templates', 'categories', 'subjects', 'monthly', 'general', 'scale', 'review'];
    }

    private function isStepComplete(string $step): bool
    {
        return match ($step) {
            'templates' => $this->validateTemplatesStep(),
            'categories' => $this->validateCategoriesStep(),
            'subjects' => $this->validateSubjectsStep(),
            'monthly' => $this->validateMonthlyStep(),
            'general' => $this->validateGeneralStep(),
            'scale' => $this->validateScaleStep(),
            default => true,
        };
    }

    private function validateTemplatesStep(): bool
    {
        if (! $this->activeTemplateId || ! $this->activeTemplate) {
            $this->addError('templateName', 'أنشئ قالبًا واحفظه قبل الانتقال للخطوة التالية.');
            return false;
        }
        return true;
    }

    private function validateCategoriesStep(): bool
    {
        if (! $this->activeTemplateId) {
            $this->addError('categoriesStep', 'اختر قالبًا أولاً قبل ضبط الفئات.');
            return false;
        }

        $categories = TemplateCategory::where('grading_template_id', $this->activeTemplateId)->get();
        if ($categories->isEmpty()) {
            $this->addError('categoriesStep', 'أضف فئة واحدة على الأقل.');
            return false;
        }

        $rootCategories = $categories->whereNull('parent_id');
        if ($rootCategories->isEmpty()) {
            $this->addError('categoriesStep', 'القالب يجب أن يحتوي على فئات جذرية.');
            return false;
        }

        $weightSum = (float) $rootCategories->sum('weight');
        if (abs($weightSum - 100.0) > 0.01) {
            $this->addError('categoriesStep', 'مجموع أوزان الفئات الجذرية يجب أن يساوي 100%.');
            return false;
        }

        return true;
    }

    private function validateSubjectsStep(): bool
    {
        if (! $this->subjectGradeId) {
            $this->addError('subjectGradeId', 'اختر الصف الدراسي أولاً.');
            return false;
        }

        if (! $this->subjectTermId) {
            $this->addError('subjectTermId', 'اختر الفصل الدراسي أولاً.');
            return false;
        }

        $subjects = Grade::find($this->subjectGradeId)?->subjects ?? collect();
        if ($subjects->isEmpty()) {
            return true;
        }

        $missing = $subjects->filter(fn ($subject) => empty($this->subjectConfigs[$subject->id] ?? null))->count();
        if ($missing > 0) {
            $this->addError('subjectConfigs', 'اربط جميع المواد بقالب مناسب قبل المتابعة.');
            return false;
        }

        return true;
    }

    private function validateMonthlyStep(): bool
    {
        if (count($this->monthlyCategories) === 0) {
            $this->addError('monthlyCategories', 'أضف بنود الدفتر الشهري واحفظها قبل المتابعة.');
            return false;
        }

        if (! $this->subjectGradeId || ! $this->subjectTermId) {
            $this->addError('monthlyMapping', 'اختر الصف والترم لإكمال ربط البنود.');
            return false;
        }

        $subjects = Grade::find($this->subjectGradeId)?->subjects ?? collect();
        $status = $this->settingsService()->getMonthlyMappingStatus($this->subjectGradeId, $this->subjectTermId);
        $summary = $this->summarizeMonthlyMappingStatus($subjects, $status);

        $hasIssues = ($summary['missing'] ?? 0) > 0
            || ($summary['partial'] ?? 0) > 0
            || ($summary['no_categories'] ?? 0) > 0;

        if ($hasIssues) {
            $this->addError('monthlyMapping', 'أكمل ربط جميع البنود بفئات التقييم قبل المتابعة.');
            return false;
        }

        return true;
    }

    private function validateGeneralStep(): bool
    {
        $settings = $this->settingsService()->loadGeneralSettings();
        $total = array_sum($settings->termWeights);
        if (abs($total - 100.0) > 0.01) {
            $this->addError('generalSettings', 'مجموع أوزان الفصول يجب أن يساوي 100% قبل المتابعة.');
            return false;
        }

        return true;
    }

    private function validateScaleStep(): bool
    {
        $scale = $this->settingsService()->loadGradeScale();
        $issues = $this->settingsService()->validateGradeScale($scale);
        if ($issues !== []) {
            $this->addError('gradeScale', $issues[0]);
            return false;
        }

        return true;
    }

    private function applyTemplateBaselineToTerms(GradingTemplate $template): void
    {
        if (! $template->term_id) {
            $this->dispatch('notify', message: 'تطبيق الأساس يحتاج قالبًا مرتبطًا بترم.', type: 'error');
            return;
        }

        $term = Term::find($template->term_id);
        if (! $term) {
            $this->dispatch('notify', message: 'الفصل الدراسي غير موجود.', type: 'error');
            return;
        }

        $terms = Term::where('academic_year_id', $term->academic_year_id)
            ->where('id', '!=', $term->id)
            ->get();

        if ($terms->isEmpty()) {
            $this->dispatch('notify', message: 'لا توجد ترمات أخرى لنسخ القالب.', type: 'warning');
            return;
        }

        $created = 0;
        $skipped = 0;
        $blocked = 0;

        $guard = app(AcademicWriteGuard::class);

        foreach ($terms as $targetTerm) {
            if (! $guard->isWritable($term->academic_year_id, $targetTerm->id)) {
                $blocked++;
                continue;
            }

            $existing = GradingTemplate::query()
                ->where('term_id', $targetTerm->id)
                ->when($template->grade_id, fn ($q) => $q->where('grade_id', $template->grade_id))
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            try {
                $clone = GradingTemplate::create([
                    'name' => $template->name,
                    'total_max_score' => $template->total_max_score,
                    'pass_score' => $template->pass_score,
                    'rounding_rule' => $template->rounding_rule,
                    'rounding_precision' => $template->rounding_precision,
                    'academic_year_id' => $term->academic_year_id,
                    'grade_id' => $template->grade_id,
                    'term_id' => $targetTerm->id,
                ]);

                $this->cloneTemplateCategories($template, $clone);
                $created++;

                if ($this->applyTemplateToSubjects) {
                    $this->applyTemplateToSubjectsForTerm($clone, $targetTerm->id);
                }
            } catch (\Throwable) {
                $skipped++;
            }
        }

        $summary = "تم نسخ القالب إلى {$created} ترمات، وتجاوز {$skipped} ترمات.";
        if ($blocked > 0) {
            $summary .= " تم منع {$blocked} ترمات بسبب الإغلاق.";
        }

        $this->dispatch('notify', message: $summary, type: 'success');
    }

    private function applyTemplateToSubjectsForTerm(GradingTemplate $template, ?int $termId): void
    {
        if (! $termId) {
            return;
        }

        $gradeId = $template->grade_id ?: $this->subjectGradeId;
        if (! $gradeId) {
            $this->dispatch('notify', message: 'اختر الصف أولاً لتطبيق القالب على المواد.', type: 'error');
            return;
        }

        $grade = Grade::find($gradeId);
        $term = Term::find($termId);
        if (! $grade || ! $term) {
            return;
        }

        $options = [];
        if ($template->total_max_score !== null) {
            $options['max_score'] = (float) $template->total_max_score;
        }
        if ($template->pass_score !== null) {
            $options['pass_score'] = (float) $template->pass_score;
        }

        app(ApplyTemplateToGradeAction::class)->execute($template, $grade, $term, $options);
    }

    private function cloneTemplateCategories(GradingTemplate $source, GradingTemplate $target): void
    {
        $categories = TemplateCategory::where('grading_template_id', $source->id)
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();

        $map = [];
        foreach ($categories->whereNull('parent_id') as $category) {
            $clone = TemplateCategory::create([
                'grading_template_id' => $target->id,
                'parent_id' => null,
                'name' => $category->name,
                'weight' => $category->weight,
                'max_raw_score' => $category->max_raw_score,
                'calculation_type' => $category->calculation_type,
                'is_dynamic_weight' => $category->is_dynamic_weight,
                'is_locked' => $category->is_locked,
                'pass_required' => $category->pass_required,
                'pass_threshold' => $category->pass_threshold,
                'order' => $category->order,
                'mapping_type' => $category->mapping_type,
                'is_readonly' => $category->is_readonly,
                'is_final_exam' => $category->is_final_exam,
            ]);
            $map[$category->id] = $clone->id;
        }

        $pending = $categories->whereNotNull('parent_id')->values();
        $safety = 0;
        while ($pending->isNotEmpty() && $safety < 10) {
            $safety++;
            $next = collect();
            foreach ($pending as $category) {
                $parentId = $map[$category->parent_id] ?? null;
                if (! $parentId) {
                    $next->push($category);
                    continue;
                }

                $clone = TemplateCategory::create([
                    'grading_template_id' => $target->id,
                    'parent_id' => $parentId,
                    'name' => $category->name,
                    'weight' => $category->weight,
                    'max_raw_score' => $category->max_raw_score,
                    'calculation_type' => $category->calculation_type,
                    'is_dynamic_weight' => $category->is_dynamic_weight,
                    'is_locked' => $category->is_locked,
                    'pass_required' => $category->pass_required,
                    'pass_threshold' => $category->pass_threshold,
                    'order' => $category->order,
                    'mapping_type' => $category->mapping_type,
                    'is_readonly' => $category->is_readonly,
                    'is_final_exam' => $category->is_final_exam,
                ]);
                $map[$category->id] = $clone->id;
            }

            if ($next->count() === $pending->count()) {
                break;
            }

            $pending = $next->values();
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, mixed> $subjects
     * @param array<int, array{status: string, mapped: int, total: int}> $status
     * @return array<string, int>
     */
    private function summarizeMonthlyMappingStatus($subjects, array $status): array
    {
        $summary = [
            'complete' => 0,
            'partial' => 0,
            'missing' => 0,
            'no_categories' => 0,
            'unavailable' => 0,
        ];

        foreach ($subjects as $subject) {
            $row = $status[$subject->id]['status'] ?? null;
            if (! $row || ! array_key_exists($row, $summary)) {
                $summary['unavailable']++;
                continue;
            }

            $summary[$row]++;
        }

        return $summary;
    }
}
