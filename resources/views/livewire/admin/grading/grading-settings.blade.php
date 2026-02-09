<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
            <span class="bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-pink-600">
                إعدادات نظام الدرجات
            </span>
        </h2>
    </div>

    <!-- Wizard Stepper -->
    @php
        $stepsOrder = ['general', 'scale', 'templates', 'monthly', 'subjects'];
        $activeIndex = array_search($activeTab, $stepsOrder, true);
        $activeIndex = $activeIndex === false ? 0 : $activeIndex;
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4">
        <div class="flex flex-wrap gap-3">
            @foreach($stepsOrder as $index => $key)
                @php($step = $wizardSteps[$key] ?? ['label' => $key])
                <button wire:click="setTab('{{ $key }}')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition-all duration-200
                        {{ $activeTab === $key 
                            ? 'bg-purple-600 text-white shadow-md' 
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $index <= $activeIndex ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600' }}">
                        {{ $index + 1 }}
                    </span>
                    <span>{{ $step['label'] ?? '' }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Step Help Overlay -->
    @if($showStepHelp)
        @php($step = $wizardSteps[$activeTab] ?? null)
        @if($step)
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-2xl mx-4 border border-white/30">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold text-purple-600">{{ $step['label'] ?? '' }}</div>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ $step['title'] ?? '' }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                {{ $step['description'] ?? '' }}
                            </p>
                        </div>
                        <button wire:click="dismissStepHelp" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>

                    @if(!empty($step['effects']))
                        <div class="mt-4 bg-gray-50 dark:bg-gray-900/40 rounded-xl p-4">
                            <div class="text-xs font-bold text-gray-500 mb-2">أثر هذه الخطوة</div>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                @foreach($step['effects'] as $effect)
                                    <li>• {{ $effect }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="dismissStepHelp"
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:text-gray-800">
                            فهمت
                        </button>
                        <button wire:click="dismissStepHelp"
                                class="px-5 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-700">
                            ابدأ الخطوة
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Content Area -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-6">

        @if($gradingQueueIsStale)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 space-y-2 text-sm text-amber-900">
                <div class="font-semibold text-base">
                    تنبيه: مزامنة الدرجات متوقفة أو متأخرة
                </div>
                <div>
                    آخر نبضة للـ queue:
                    <span class="font-bold">{{ $gradingQueueLastHeartbeat ?? 'غير متوفر' }}</span>
                </div>
                <div>
                    إذا استمر التنبيه، شغّل الـ queue أو استخدم إعادة التجميع من تبويب المواد.
                </div>
            </div>
        @endif
        
        <!-- 1. Templates Tab -->
        @if($activeTab === 'templates')
            <div class="mb-6">
                <livewire:admin.grading.grading-health-report :term-id="$subjectTermId" />
            </div>

            @if(! $gradingHealthIsClean)
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50/70 p-4 space-y-3 text-sm text-red-900">
                    <div class="font-semibold text-base">
                        Stop report active — لا يمكن تنفيذ العمليات الحساسة (المعالجة/النشر/الإغلاق)
                    </div>
                    <div>
                        يوجد مشاكل في إعدادات الدرجات:
                        Missing: <span class="font-bold">{{ $gradingHealthMissingCount }}</span>,
                        Invalid: <span class="font-bold">{{ $gradingHealthInvalidCount }}</span>.
                        أصلحها ثم أعد تشغيل الفحص.
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            wire:click="$dispatch('runGradingHealthCheckRequested')"
                            class="px-4 py-2 rounded-lg border border-white bg-white text-purple-700 font-semibold shadow-sm transition hover:bg-purple-50">
                            تشغيل فحص الصحة الآن
                        </button>
                        <button
                            type="button"
                            wire:click="setTab('subjects')"
                            class="px-4 py-2 rounded-lg border border-white/80 bg-purple-600 text-white font-semibold shadow-sm transition hover:bg-purple-700">
                            فتح الإعدادات لإصلاح المشاكل
                        </button>
                    </div>
                </div>
            @endif
            <div class="grid grid-cols-12 gap-6">
                <!-- Sidebar: Template List -->
                <div class="col-span-12 md:col-span-3 border-l border-gray-200 dark:border-gray-700 pl-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-700 dark:text-gray-200">القوالب</h3>
                        <button wire:click="createTemplate" class="text-purple-600 hover:text-purple-700 text-sm font-bold">+ جديد</button>
                    </div>
                    <div class="space-y-2">
                        @foreach($templates as $template)
                            <div wire:click="selectTemplate({{ $template->id }})" 
                                 class="p-3 rounded-lg cursor-pointer transition-colors {{ $activeTemplateId === $template->id ? 'bg-purple-100 dark:bg-purple-900/30 border-purple-500 border' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $template->name }}</div>
                                <div class="text-xs text-gray-500">{{ $template->academicYear->name ?? 'عام' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Main: Template Editor -->
                <div class="col-span-12 md:col-span-9">
                    @if($activeTemplateId || $activeTemplate === null)
                        <div class="space-y-6">
                            <!-- Template Info Form -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اسم القالب</label>
                                    <input type="text" wire:model="templateName" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">السنة الدراسية</label>
                                    <select wire:model="templateAcademicYearId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <option value="">عام (كل السنوات)</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الفصل الدراسي</label>
                                    <select wire:model="templateTermId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <option value="">اختر الفصل</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('templateTermId')
                                        <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="flex items-end">
                                    <button wire:click="saveTemplate"
                                            class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg transition">
                                        حفظ القالب
                                    </button>
                                </div>
                            </div>

                            @if($activeTemplateId)
                                <hr class="border-gray-200 dark:border-gray-700">
                                
                                <!-- Tree Builder -->
                                <div>
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="font-bold text-lg text-gray-800 dark:text-white">هيكلية الدرجات</h3>
                                        <button wire:click="openCategoryForm" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm">
                                            + إضافة فئة رئيسية
                                        </button>
                                    </div>

                                    <!-- Tree Visualization -->
                                    <div class="space-y-3">
                                        @foreach($activeTemplate->categories as $category)
                                            <x-grading-tree-item :category="$category" :level="0" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            اختر قالباً للتعديل أو أنشئ قالباً جديداً
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- 2. Grade Scale Tab --}}
        @if($activeTab === 'scale')
            <livewire:admin.grading.components.grade-scale-manager />
        @endif

        {{-- 3. General Settings Tab --}}
        @if($activeTab === 'general')
            <livewire:admin.grading.components.general-grading-settings />
        @endif

        <!-- 4. Subjects Tab -->
        @if($activeTab === 'subjects')
        <div class="space-y-6">
            <!-- Filters -->
            <div class="flex gap-4 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
                    <div class="w-1/3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">الصف الدراسي</label>
                        <select wire:model.live="subjectGradeId" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-1/3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">الفصل الدراسي</label>
                        <select wire:model.live="subjectTermId" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
            </div>

            @if(! $gradingHealthIsClean)
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 space-y-1">
                    <div class="font-semibold">موقوف: يوجد Missing أو Invalid config.</div>
                    <div class="text-xs text-red-600">
                        أصلح {{ $gradingHealthMissingCount }} Missing و{{ $gradingHealthInvalidCount }} Invalid من التقرير ثم أعد الفحص.
                    </div>
                </div>
            @endif

            <!-- Subjects Table -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المادة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">القالب المخصص</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($subjects as $subject)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ $subject->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <select wire:model="subjectConfigs.{{ $subject->id }}" class="w-full rounded border-gray-300 dark:bg-gray-700 text-sm">
                                            <option value="">-- اختر قالباً --</option>
                                            @foreach($templates as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button wire:click="saveSubjectConfig({{ $subject->id }})"
                                                data-guard="subject-save"
                                                class="text-purple-600 hover:text-purple-800 font-medium text-sm transition">
                                            حفظ
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">لا توجد مواد لهذا الصف</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($showMonthlyMappingModal)
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeMonthlyMapping">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-5xl mx-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">
                            مابينغ الدفتر الشهري — {{ $mappingSubjectName ?: 'المادة' }}
                        </h3>
                        <button wire:click="closeMonthlyMapping" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>

                    @if(empty($mappingCategories))
                        <div class="text-sm text-gray-500">لا توجد بنود شهرية للعرض.</div>
                    @elseif(empty($mappingTemplateCategories))
                        <div class="text-sm text-gray-500">لا توجد فئات قالب متاحة لهذه المادة.</div>
                    @else
                        <div class="overflow-auto max-h-[60vh] border border-gray-200 dark:border-gray-700 rounded-xl">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">البند</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">فئة القالب</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">قاعدة التجميع</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">سياسة الأشهر الناقصة</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($mappingCategories as $category)
                                        @php($key = $category['key'] ?? '')
                                        <tr>
                                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">
                                                {{ $category['label'] ?? '' }}
                                                <div class="text-[10px] text-gray-400">{{ $key }}</div>
                                            </td>
                                            <td class="px-4 py-2">
                                                <select wire:model="monthlyCategoryMappings.{{ $key }}.template_category_id"
                                                        class="w-full rounded border-gray-300 dark:bg-gray-700 text-xs">
                                                    <option value="">-- اختر فئة --</option>
                                                    @foreach($mappingTemplateCategories as $option)
                                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-2">
                                                <select wire:model="monthlyCategoryMappings.{{ $key }}.aggregation_rule"
                                                        class="w-full rounded border-gray-300 dark:bg-gray-700 text-xs">
                                                    @foreach($aggregationRuleOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-2">
                                                <select wire:model="monthlyCategoryMappings.{{ $key }}.missing_months_policy"
                                                        class="w-full rounded border-gray-300 dark:bg-gray-700 text-xs">
                                                    @foreach($missingMonthsPolicyOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end gap-3 mt-4">
                            <button wire:click="closeMonthlyMapping"
                                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:text-gray-800">
                                إلغاء
                            </button>
                            <button wire:click="saveMonthlyMappings"
                                    class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                                حفظ المابينغ
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- 5. Monthly Gradebook Tab -->
        @if($activeTab === 'monthly')
            <div class="space-y-8" wire:init="loadMonthlySettings">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">إعدادات الدفتر الشهري (النمط الورقي)</h3>
                    <button wire:click="saveMonthlySettings"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition">
                        حفظ الإعدادات
                    </button>
                </div>

                <div class="bg-white/70 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm backdrop-blur">
                    <div class="text-xs font-bold text-purple-600 mb-1">الخطوة 1</div>
                    <div class="text-base font-bold text-gray-800 dark:text-white">تعريف بنود الدفتر وقواعد المواظبة</div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                        هذه البنود تمثل أعمدة دفتر المعلم الشهري، ومنها يتم تجميع أعمال السنة لاحقًا.
                    </p>
                </div>

                <!-- Categories -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h4 class="font-bold text-gray-700 dark:text-gray-300">بنود التقييم الشهرية</h4>
                        <button wire:click="addMonthlyCategory" class="text-sm bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg">
                            + إضافة بند
                        </button>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم البند</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الدرجة العظمى</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">مرتبط بالحضور؟</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">حذف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($monthlyCategories as $index => $cat)
                                <tr>
                                    <td class="px-6 py-4">
                                        <input type="text" wire:model="monthlyCategories.{{ $index }}.label" class="w-full rounded border-gray-300 dark:bg-gray-700" placeholder="مثلاً: واجبات">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" wire:model="monthlyCategories.{{ $index }}.max_score" class="w-24 rounded border-gray-300 dark:bg-gray-700 text-center">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" wire:model="monthlyCategories.{{ $index }}.is_attendance" class="rounded border-gray-300 text-purple-600 w-5 h-5">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button wire:click="removeMonthlyCategory({{ $index }})" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Attendance Rules -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl space-y-4">
                        <h4 class="font-bold text-gray-800 dark:text-white border-b pb-2">قواعد خصم المواظبة</h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">يبدأ الخصم بعد غياب (أيام)</label>
                            <input type="number" wire:model="attendanceDeductAfter" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مقدار الخصم لكل يوم غياب</label>
                            <input type="number" step="0.5" wire:model="attendanceDeductPerAbsence" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl space-y-4">
                        <h4 class="font-bold text-gray-800 dark:text-white border-b pb-2">إعدادات إضافية</h4>
                        <label class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" wire:model="allowCustomCategories" class="rounded border-gray-300 text-purple-600 w-5 h-5">
                            <div>
                                <div class="font-bold text-gray-800 dark:text-gray-200">السماح للمعلمين بإضافة بنود خاصة</div>
                                <div class="text-xs text-gray-500">يمكن للمعلم إضافة أعمدة إضافية (مثل: مشروع، نشاط) لدفتره الخاص</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm backdrop-blur">
                    <div class="text-xs font-bold text-purple-600 mb-1">الخطوة 2</div>
                    <div class="text-base font-bold text-gray-800 dark:text-white">ربط بنود الدفتر بفئات التقييم</div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                        هذا الربط يحدد أي بند شهري يتجمع داخل أي فئة من القالب (أعمال سنة/نهائي).
                        بدونه تظهر مشاكل Missing/Invalid ولن تعمل عمليات التجميع بشكل صحيح.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex gap-4 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
                        <div class="w-1/3">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">الصف الدراسي</label>
                            <select wire:model.live="subjectGradeId" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-1/3">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">الفصل الدراسي</label>
                            <select wire:model.live="subjectTermId" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if(! $subjectTermId)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            اختر الفصل الدراسي أولاً لعرض المواد وربط البنود.
                        </div>
                    @else
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-semibold">
                                مكتمل {{ $monthlyMappingSummary['complete'] ?? 0 }}
                            </span>
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold">
                                ناقص {{ $monthlyMappingSummary['partial'] ?? 0 }}
                            </span>
                            <span class="px-3 py-1 rounded-full bg-red-100 text-red-800 font-semibold">
                                غير مرتبط {{ $monthlyMappingSummary['missing'] ?? 0 }}
                            </span>
                            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 font-semibold">
                                بنود غير معرفة {{ $monthlyMappingSummary['no_categories'] ?? 0 }}
                            </span>
                            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 font-semibold">
                                غير متاح {{ $monthlyMappingSummary['unavailable'] ?? 0 }}
                            </span>
                        </div>

                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المادة</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">حالة الربط</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($subjects as $subject)
                                        @php($status = $monthlyMappingStatus[$subject->id] ?? null)
                                        <tr>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                {{ $subject->name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                @if(! $status)
                                                    <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">
                                                        غير متاح
                                                    </span>
                                                @elseif($status['status'] === 'complete')
                                                    <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold">
                                                        مكتمل ({{ $status['mapped'] }}/{{ $status['total'] }})
                                                    </span>
                                                @elseif($status['status'] === 'partial')
                                                    <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">
                                                        ناقص ({{ $status['mapped'] }}/{{ $status['total'] }})
                                                    </span>
                                                @elseif($status['status'] === 'missing')
                                                    <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">
                                                        غير مرتبط
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">
                                                        بنود غير معرفة
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <button wire:click="openMonthlyMapping({{ $subject->id }})"
                                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm transition">
                                                    ربط بنود الدفتر بفئات التقييم
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">لا توجد مواد لهذا الصف</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Category Modal -->
    <x-dialog-modal wire:model="showCategoryForm">
        <x-slot name="title">
            {{ $editingCategoryId ? 'تعديل الفئة' : 'إضافة فئة جديدة' }}
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">اسم الفئة</label>
                    <input type="text" wire:model="categoryName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">الوزن النسبي</label>
                        <input type="number" step="0.01" wire:model="categoryWeight" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">الدرجة العظمى (الخام)</label>
                        <input type="number" step="0.01" wire:model="categoryMaxRawScore" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700">
                        <span class="text-xs text-gray-500">اختياري، للمعلم</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">نوع الحساب</label>
                    <select wire:model="categoryCalculationType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700">
                        <option value="sum">مجموع (Sum)</option>
                        <option value="average">متوسط (Average)</option>
                        <option value="weighted_average">متوسط مرجح (Weighted Avg)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">نوع الفئة (Mapping)</label>
                    <select wire:model="categoryMappingType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700">
                        <option value="manual">يدوي (Manual)</option>
                        <option value="monthly_average">متوسط شهري (مهمل)</option>
                        <option value="attendance">حضور</option>
                        <option value="homework">واجبات</option>
                        <option value="final_exam">اختبار نهائي</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" wire:model="categoryPassRequired" class="rounded border-gray-300 text-purple-600 shadow-sm">
                        <span class="text-sm text-gray-600 dark:text-gray-400">يتطلب نجاح</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">حد النجاح</label>
                        <input type="number" step="0.01" wire:model="categoryPassThreshold" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700" @if(!$categoryPassRequired) disabled @endif>
                    </div>
                </div>
                <div class="flex gap-4 mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="categoryIsDynamic" class="rounded border-gray-300 text-purple-600 shadow-sm">
                        <span class="mr-2 text-sm text-gray-600 dark:text-gray-400">وزن ديناميكي (تلقائي)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="categoryIsLocked" class="rounded border-gray-300 text-purple-600 shadow-sm">
                        <span class="mr-2 text-sm text-gray-600 dark:text-gray-400">قفل الأوزان (منع المعلم)</span>
                    </label>
                </div>
                <div class="flex gap-4 mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="categoryIsReadonly" class="rounded border-gray-300 text-purple-600 shadow-sm">
                        <span class="mr-2 text-sm text-gray-600 dark:text-gray-400">للقراءة فقط</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="categoryIsFinalExam" class="rounded border-gray-300 text-purple-600 shadow-sm">
                        <span class="mr-2 text-sm text-gray-600 dark:text-gray-400">اختبار نهائي</span>
                    </label>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showCategoryForm', false)"
                                wire:loading.attr="disabled">
                إلغاء
            </x-secondary-button>

            <x-button class="mr-3 bg-purple-600 hover:bg-purple-700"
                      wire:click="saveCategory"
                      wire:loading.attr="disabled">
                حفظ
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
