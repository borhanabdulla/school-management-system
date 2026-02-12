<div class="space-y-6">
    @php
        $stepsOrder = ['templates', 'categories', 'subjects', 'monthly', 'general', 'scale', 'review'];
        $riskMap = [
            'general' => 'متوسط',
            'scale' => 'منخفض',
            'templates' => 'مرتفع',
            'categories' => 'مرتفع',
            'subjects' => 'مرتفع',
            'monthly' => 'متوسط',
            'review' => 'منخفض',
        ];
        $attentionSteps = ['templates', 'categories', 'subjects', 'monthly'];
        $hintMap = [
            'general' => 'قواعد النجاح والأوزان العامة',
            'scale' => 'تعريف التقديرات والنسب',
            'templates' => 'أوزان القالب وفئاته هي سبب رئيسي لـ Invalid',
            'categories' => 'مجموع أوزان الفئات الجذرية يجب أن يساوي 100%',
            'subjects' => 'ربط المواد يمنع Missing عند الإغلاق',
            'monthly' => 'ربط البنود يحدد أعمال السنة',
            'review' => 'مراجعة نهائية قبل الإغلاق',
        ];
        $steps = [];
        foreach ($stepsOrder as $key) {
            $steps[] = [
                'key' => $key,
                'label' => $wizardSteps[$key]['label'] ?? $key,
                'risk' => $riskMap[$key] ?? 'منخفض',
                'hint' => $hintMap[$key] ?? null,
                'state' => ($gradingHealthChecked && ! $gradingHealthIsClean && in_array($key, $attentionSteps, true)) ? 'warning' : null,
            ];
        }
        $currentStep = $wizardSteps[$activeTab] ?? null;
        $activeIndex = array_search($activeTab, $stepsOrder, true);
        $prevStepKey = ($activeIndex !== false && $activeIndex > 0) ? $stepsOrder[$activeIndex - 1] : null;
        $nextStepKey = ($activeIndex !== false && $activeIndex < count($stepsOrder) - 1) ? $stepsOrder[$activeIndex + 1] : null;
        $prevStepLabel = $prevStepKey ? ($wizardSteps[$prevStepKey]['label'] ?? $prevStepKey) : null;
        $nextStepLabel = $nextStepKey ? ($wizardSteps[$nextStepKey]['label'] ?? $nextStepKey) : null;

        // Calculate Health Scores - Now handled by GradingHealthReport component
    @endphp

    {{-- ══════════════════════════════════════════════════
         HEADER — Title + Health Badge
    ══════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-3xl border border-gray-200/60 bg-gradient-to-br from-purple-50 via-white to-indigo-50/50 shadow-lg dark:border-slate-700/60 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900">
        {{-- Decorative blurs --}}
        <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-purple-300/20 blur-3xl dark:bg-purple-800/20"></div>
        <div class="pointer-events-none absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-indigo-300/20 blur-3xl dark:bg-indigo-800/15"></div>

        <div class="relative p-6 sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-purple-100/80 px-3 py-1 text-[11px] font-semibold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        </svg>
                        منظومة التقدير
                    </div>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">إعدادات الدرجات</h1>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        رتّب القوالب وربط المواد والدفتر الشهري بخطوات واضحة حتى تصل لنتائج سليمة.
                    </p>
                </div>

                {{-- Health badge --}}
                <div class="flex-shrink-0">
                    @if(! $gradingHealthChecked)
                        <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200/60 bg-slate-50/80 px-5 py-3 shadow-sm dark:border-slate-700/40 dark:bg-slate-900/40">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800/50">
                                <svg class="h-5 w-5 text-slate-500 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-200">لم يتم الفحص بعد</div>
                                <div class="text-[11px] text-slate-500/80 dark:text-slate-400/70">شغّل فحص الصحة عند المراجعة</div>
                            </div>
                        </div>
                    @elseif($gradingHealthIsClean)
                        <div class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-5 py-3 shadow-sm dark:border-emerald-700/40 dark:bg-emerald-900/20">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-800/40">
                                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-emerald-700 dark:text-emerald-300">جاهز للإغلاق</div>
                                <div class="text-[11px] text-emerald-600/70 dark:text-emerald-400/60">كل الإعدادات سليمة</div>
                            </div>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 rounded-2xl border border-amber-200/60 bg-amber-50/80 px-5 py-3 shadow-sm dark:border-amber-700/40 dark:bg-amber-900/20">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-800/40">
                                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-amber-700 dark:text-amber-300">بحاجة مراجعة</div>
                                <div class="text-[11px] text-amber-600/70 dark:text-amber-400/60">يوجد مشاكل تحتاج إصلاح</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Context bar --}}
            <div class="mt-5">
                <x-grading.context-bar
                    :terms="$terms"
                    :grades="$grades"
                    :academic-years="$academicYears"
                    :selected-term-id="$subjectTermId"
                    :selected-grade-id="$subjectGradeId"
                    :missing-count="$gradingHealthMissingCount"
                    :invalid-count="$gradingHealthInvalidCount"
                    :is-clean="$gradingHealthIsClean"
                    :health-checked="$gradingHealthChecked"
                />
            </div>
        </div>
    </div>

    {{-- Step help modal --}}
    <x-grading.step-help :show="$showStepHelp" :step="$currentStep" />

    {{-- ══════════════════════════════════════════════════
         MAIN LAYOUT — Stepper + Content + Sidebar
    ══════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-12 gap-6">
        {{-- Main content area (8 cols) --}}
        <div class="col-span-12 space-y-5 xl:col-span-8">
            {{-- Horizontal stepper --}}
            <x-grading.stepper :steps="$steps" :active="$activeTab" orientation="horizontal" :interactive="false" />

            {{-- Alerts (compact) --}}
            @if($activeTab === 'review' && ($gradingHealthChecked || $gradingQueueIsStale))
                <div class="space-y-3">
                    @if($gradingQueueIsStale)
                        <x-grading.queue-alert :is-stale="$gradingQueueIsStale" :last-heartbeat="$gradingQueueLastHeartbeat" />
                    @endif
                    @if($gradingHealthChecked && ! $gradingHealthIsClean)
                        <x-grading.health-warning :is-clean="$gradingHealthIsClean" :missing="$gradingHealthMissingCount" :invalid="$gradingHealthInvalidCount" />
                    @endif
                </div>
            @endif

            {{-- Tab content card --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200/60 bg-white/95 p-6 shadow-lg backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/80">
                <!-- 1. Templates Tab -->
                @if($activeTab === 'templates')
                    <x-grading.templates-tab
                        :templates="$templates"
                        :active-template-id="$activeTemplateId"
                        :active-template="$activeTemplate"
                        :template-name="$templateName"
                        :template-academic-year-id="$templateAcademicYearId"
                        :template-grade-id="$templateGradeId"
                        :template-term-id="$templateTermId"
                        :academic-years="$academicYears"
                        :terms="$terms"
                        :apply-template-to-all-terms="$applyTemplateToAllTerms"
                        :apply-template-to-subjects="$applyTemplateToSubjects"
                        :show-template-form="true"
                        :show-apply-options="true"
                        :show-category-tree="false"
                        :show-guide="false"
                        :show-template-create="true"
                        help-title="ابدأ بإنشاء القالب"
                        help-message="أنشئ القالب وحدد الترم، ثم انتقل إلى خطوة الفئات لضبط الأوزان."
                    />
                @endif

                @if($activeTab === 'categories')
                    <x-grading.templates-tab
                        :templates="$templates"
                        :active-template-id="$activeTemplateId"
                        :active-template="$activeTemplate"
                        :template-name="$templateName"
                        :template-academic-year-id="$templateAcademicYearId"
                        :template-grade-id="$templateGradeId"
                        :template-term-id="$templateTermId"
                        :academic-years="$academicYears"
                        :terms="$terms"
                        :apply-template-to-all-terms="$applyTemplateToAllTerms"
                        :apply-template-to-subjects="$applyTemplateToSubjects"
                        :show-template-form="false"
                        :show-apply-options="false"
                        :show-category-tree="true"
                        :show-guide="true"
                        :show-template-create="false"
                        help-title="ماذا يجب إنجازه هنا؟"
                        help-message="أضف الفئات الجذرية واضبط أوزانها حتى يصبح المجموع 100%، ثم أضف الفروع عند الحاجة."
                    />
                @endif

                {{-- 2. Grade Scale Tab --}}
                @if($activeTab === 'scale')
                    <x-grading.help-hint
                        title="معلومة"
                        message="تغيير سلم التقديرات يؤثر على عرض النتائج ولا يغيّر الدرجات الخام."
                        variant="info"
                    />
                    <div class="mt-4">
                        <livewire:admin.grading.components.grade-scale-manager
                            :external-error="$errors->first('gradeScale')"
                        />
                    </div>
                @endif

                {{-- 3. General Settings Tab --}}
                @if($activeTab === 'general')
                    <x-grading.help-hint
                        title="تنبيه"
                        message="اجعل مجموع أوزان الفصول = 100% لتجنّب منع الإغلاق."
                        variant="warning"
                    />
                    <div class="mt-4">
                        <livewire:admin.grading.components.general-grading-settings
                            :external-error="$errors->first('generalSettings')"
                        />
                    </div>
                @endif

                <!-- 4. Subjects Tab -->
                @if($activeTab === 'subjects')
                    <x-grading.subjects-tab
                        :subjects="$subjects"
                        :grades="$grades"
                        :terms="$terms"
                        :templates="$templates"
                        :subject-grade-id="$subjectGradeId"
                        :subject-term-id="$subjectTermId"
                        :subject-configs="$subjectConfigs"
                        :subject-search="$subjectSearch"
                        :bulk-template-id="$bulkTemplateId"
                        :grading-health-is-clean="$gradingHealthIsClean"
                        :grading-health-missing-count="$gradingHealthMissingCount"
                        :grading-health-invalid-count="$gradingHealthInvalidCount"
                    />
                @endif

                <!-- 5. Monthly Gradebook Tab -->
                @if($activeTab === 'monthly')
                    <x-grading.monthly-tab
                        :monthly-categories="$monthlyCategories"
                        :attendance-deduct-after="$attendanceDeductAfter"
                        :attendance-deduct-per-absence="$attendanceDeductPerAbsence"
                        :attendance-max-score="$attendanceMaxScore"
                        :allow-custom-categories="$allowCustomCategories"
                        :grades="$grades"
                        :terms="$terms"
                        :subject-grade-id="$subjectGradeId"
                        :subject-term-id="$subjectTermId"
                        :monthly-mapping-summary="$monthlyMappingSummary"
                        :monthly-mapping-status="$monthlyMappingStatus"
                        :subjects="$subjects"
                    />
                @endif

                @if($activeTab === 'review')
                    <x-grading.review-tab
                        :is-clean="$gradingHealthIsClean"
                        :missing="$gradingHealthMissingCount"
                        :invalid="$gradingHealthInvalidCount"
                        :terms="$terms"
                        :grades="$grades"
                        :selected-term-id="$subjectTermId"
                        :selected-grade-id="$subjectGradeId"
                    />
                @endif
            </div>

            {{-- Navigation buttons --}}
            <div class="flex items-center justify-between rounded-2xl border border-gray-200/60 bg-white/95 px-5 py-3.5 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/80">
                <button
                    type="button"
                    wire:click="goPreviousStep"
                    @disabled(! $prevStepKey)
                    class="group inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all duration-300
                        {{ $prevStepKey
                            ? 'border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 dark:border-slate-700 dark:text-gray-200 dark:hover:bg-slate-800/60'
                            : 'cursor-not-allowed border-gray-100 text-gray-300 dark:border-slate-800 dark:text-slate-600'
                        }}"
                >
                    <svg class="h-4 w-4 transition-transform duration-300 {{ $prevStepKey ? 'group-hover:translate-x-1' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                    <span>السابق</span>
                    @if($prevStepLabel)
                        <span class="hidden text-[11px] font-normal text-gray-400 sm:inline">({{ $prevStepLabel }})</span>
                    @endif
                </button>

                <div class="hidden items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-slate-500 sm:flex">
                    @foreach($stepsOrder as $i => $s)
                        <span class="h-1.5 w-1.5 rounded-full {{ $i == $activeIndex ? 'bg-purple-500 w-4' : ($i < $activeIndex ? 'bg-emerald-400' : 'bg-gray-200 dark:bg-slate-700') }} transition-all duration-300"></span>
                    @endforeach
                </div>

                <button
                    type="button"
                    wire:click="goNextStep"
                    @disabled(! $nextStepKey)
                    class="group inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-300
                        {{ $nextStepKey
                            ? 'bg-gradient-to-l from-purple-600 to-purple-700 text-white shadow-md shadow-purple-500/20 hover:shadow-lg hover:shadow-purple-500/30 hover:-translate-y-0.5'
                            : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-slate-800 dark:text-slate-600'
                        }}"
                >
                    @if($nextStepLabel)
                        <span class="hidden text-[11px] font-normal text-white/70 sm:inline">({{ $nextStepLabel }})</span>
                    @endif
                    <span>التالي</span>
                    <svg class="h-4 w-4 rotate-180 transition-transform duration-300 {{ $nextStepKey ? 'group-hover:-translate-x-1' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Sidebar (4 cols) --}}
        <div class="col-span-12 space-y-5 xl:col-span-4">
            {{-- Health panel --}}
            @if($activeTab === 'review')
                <livewire:admin.grading.grading-health-report :term-id="$subjectTermId" wire:key="health-report-{{ $subjectTermId }}" />
            @endif

            {{-- Step focus card --}}
            @if($currentStep)
                <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/80">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/40">
                            <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold text-gray-400 dark:text-slate-500">تركيز الخطوة</div>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                {{ $currentStep['title'] ?? $currentStep['label'] }}
                            </div>
                        </div>
                    </div>

                    @if(! empty($currentStep['description']))
                        <p class="mt-3 text-[13px] leading-relaxed text-gray-600 dark:text-slate-300">
                            {{ $currentStep['description'] }}
                        </p>
                    @endif

                    @if(! empty($currentStep['requirements']))
                        <div class="mt-4 space-y-1.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">ما المطلوب؟</div>
                            @foreach($currentStep['requirements'] as $requirement)
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50/80 px-3 py-2 dark:bg-slate-800/40">
                                    <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                                    </svg>
                                    <span class="text-xs text-gray-600 dark:text-slate-300">{{ $requirement }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(! empty($currentStep['effects']))
                        <div class="mt-4 space-y-1.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">ماذا ستؤثر؟</div>
                            @foreach($currentStep['effects'] as $effect)
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50/80 px-3 py-2 dark:bg-slate-800/40">
                                    <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                    <span class="text-xs text-gray-600 dark:text-slate-300">{{ $effect }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Modals --}}
    <x-grading.monthly-mapping-modal
        :show="$showMonthlyMappingModal"
        :mapping-subject-name="$mappingSubjectName"
        :mapping-categories="$mappingCategories"
        :mapping-template-categories="$mappingTemplateCategories"
        :aggregation-rule-options="$aggregationRuleOptions"
        :missing-months-policy-options="$missingMonthsPolicyOptions"
    />

    <x-grading.category-modal
        :editing-category-id="$editingCategoryId"
        :category-pass-required="$categoryPassRequired"
        :category-calculation-type="$categoryCalculationType"
        :category-mapping-type="$categoryMappingType"
    />
</div>
