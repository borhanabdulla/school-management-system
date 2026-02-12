@props([
    'templates' => [],
    'activeTemplateId' => null,
    'activeTemplate' => null,
    'templateName' => '',
    'templateAcademicYearId' => null,
    'templateGradeId' => null,
    'templateTermId' => null,
    'academicYears' => [],
    'terms' => [],
    'applyTemplateToAllTerms' => false,
    'applyTemplateToSubjects' => false,
    'showTemplateForm' => true,
    'showCategoryTree' => true,
    'showApplyOptions' => true,
    'showGuide' => true,
    'showTemplateCreate' => true,
    'helpTitle' => 'لماذا هذه الخطوة مهمة؟',
    'helpMessage' => 'أي خلل في أوزان القالب أو فئاته يؤدي مباشرة إلى Invalid عند الإغلاق.',
])

<div class="space-y-6">
    {{-- Help hint --}}
    <x-grading.help-hint
        :title="$helpTitle"
        :message="$helpMessage"
        variant="warning"
    />

    @if($showGuide)
        <div class="rounded-2xl border border-gray-200/60 bg-white/90 p-4 text-xs text-gray-600 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-300">
            <div class="text-sm font-bold text-gray-800 dark:text-white">دليل عملي لبناء القالب (الأولوية قبل كل شيء)</div>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                <div class="rounded-lg bg-gray-50/80 p-3 dark:bg-slate-800/50">
                    <div class="text-[11px] font-semibold text-gray-700 dark:text-slate-200">الفئات الرئيسية</div>
                    <div class="text-[11px] text-gray-500 dark:text-slate-400">مثل: أعمال السنة / اختبار نهائي. مجموع أوزانها يجب = 100%.</div>
                </div>
                <div class="rounded-lg bg-gray-50/80 p-3 dark:bg-slate-800/50">
                    <div class="text-[11px] font-semibold text-gray-700 dark:text-slate-200">الفئات الفرعية</div>
                    <div class="text-[11px] text-gray-500 dark:text-slate-400">يمكن تفصيل أعمال السنة (واجبات، مشاركة، اختبارات قصيرة) حسب الحاجة.</div>
                </div>
                <div class="rounded-lg bg-gray-50/80 p-3 dark:bg-slate-800/50">
                    <div class="text-[11px] font-semibold text-gray-700 dark:text-slate-200">الوزن النسبي</div>
                    <div class="text-[11px] text-gray-500 dark:text-slate-400">يمثل نسبة الفئة من الدرجة النهائية (مثلاً 20% لأعمال السنة).</div>
                </div>
                <div class="rounded-lg bg-gray-50/80 p-3 dark:bg-slate-800/50">
                    <div class="text-[11px] font-semibold text-gray-700 dark:text-slate-200">حد النجاح</div>
                    <div class="text-[11px] text-gray-500 dark:text-slate-400">فعّل "يتطلب نجاح" فقط إذا كانت الفئة شرطًا مستقلاً للنجاح.</div>
                </div>
                <div class="rounded-lg bg-gray-50/80 p-3 dark:bg-slate-800/50">
                    <div class="text-[11px] font-semibold text-gray-700 dark:text-slate-200">قفل الأوزان</div>
                    <div class="text-[11px] text-gray-500 dark:text-slate-400">القفل يمنع المعلم من تعديل أوزان البنود الحساسة.</div>
                </div>
                <div class="rounded-lg bg-gray-50/80 p-3 dark:bg-slate-800/50">
                    <div class="text-[11px] font-semibold text-gray-700 dark:text-slate-200">اختبار نهائي/للقراءة فقط</div>
                    <div class="text-[11px] text-gray-500 dark:text-slate-400">ضع علامة "اختبار نهائي" لظهوره كشهادة، و"للقراءة فقط" لعناصر معلوماتية.</div>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-gray-500 dark:text-slate-400">
                تعديل الفئات يتم من أيقونة القلم بجانب الفئة، وإضافة فروع من زر الإضافة.
            </div>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6">
        {{-- ════════════════════════════════
             Templates sidebar (3 cols)
        ════════════════════════════════ --}}
        <div class="col-span-12 md:col-span-3">
            <div class="rounded-2xl border border-gray-200/60 bg-gray-50/80 p-4 dark:border-slate-700/40 dark:bg-slate-800/40">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">القوالب</span>
                    </div>
                    @if($showTemplateCreate)
                        <button
                            wire:click="createTemplate"
                            class="inline-flex items-center gap-1 rounded-lg bg-gradient-to-l from-purple-600 to-purple-700 px-2.5 py-1.5 text-[11px] font-semibold text-white shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            جديد
                        </button>
                    @endif
                </div>

                <div class="space-y-1.5">
                    @forelse($templates as $template)
                        <div
                            wire:click="selectTemplate({{ $template->id }})"
                            class="group cursor-pointer rounded-xl border px-3 py-2.5 transition-all duration-200
                                {{ $activeTemplateId === $template->id
                                    ? 'border-purple-300/60 bg-purple-50 shadow-sm dark:border-purple-600/40 dark:bg-purple-900/20'
                                    : 'border-transparent hover:border-gray-200 hover:bg-white dark:hover:border-slate-700 dark:hover:bg-slate-800/60' }}"
                        >
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg {{ $activeTemplateId === $template->id ? 'bg-purple-200/80 dark:bg-purple-800/40' : 'bg-gray-100 dark:bg-slate-700/60' }}">
                                    <svg class="h-3.5 w-3.5 {{ $activeTemplateId === $template->id ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold {{ $activeTemplateId === $template->id ? 'text-purple-700 dark:text-purple-300' : 'text-gray-700 dark:text-gray-200' }}">
                                        {{ $template->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 dark:text-slate-500">
                                        {{ $template->academicYear->name ?? 'عام' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <div class="mt-2 text-xs text-gray-400 dark:text-slate-500">لا توجد قوالب بعد</div>
                            <div class="mt-1 text-[11px] text-gray-400 dark:text-slate-500">أنشئ أول قالب للبدء</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════
             Template editor (9 cols)
        ════════════════════════════════ --}}
        <div class="col-span-12 md:col-span-9 space-y-5">
            @if($showCategoryTree && $activeTemplateId)
                {{-- Weight meter --}}
                @php
                    $rootWeight = $activeTemplate ? (float) $activeTemplate->categories->sum('weight') : 0.0;
                @endphp
                <x-grading.weight-meter :total="$rootWeight" />
                @error('templateWeight')
                    <div class="text-xs text-rose-600">{{ $message }}</div>
                @enderror
            @endif

            @if($showCategoryTree)
                @error('categoriesStep')
                    <div class="text-xs text-rose-600">{{ $message }}</div>
                @enderror
            @endif

            @if($showTemplateForm)
                {{-- Template form --}}
                <div class="rounded-2xl border border-gray-200/40 bg-gray-50/60 p-5 dark:border-slate-700/30 dark:bg-slate-800/30">
                    <div class="mb-3 flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">المعلومات الأساسية</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-500 dark:text-slate-400">اسم القالب</label>
                            <input type="text" wire:model="templateName"
                                   class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200"
                                   placeholder="مثال: قالب الرياضيات">
                            <div class="mt-1 text-[10px] text-gray-400">اسم واضح يظهر عند ربط المواد بالقالب.</div>
                            @error('templateName')
                                <span class="mt-1 text-[11px] text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-500 dark:text-slate-400">السنة الدراسية</label>
                            <select wire:model="templateAcademicYearId"
                                    class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200"
                                    @disabled($templateTermId)>
                                <option value="">عام (كل السنوات)</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                            <div class="mt-1 text-[10px] text-gray-400">اختياري: قيد القالب بسنة محددة.</div>
                            @if($templateTermId)
                                <div class="mt-1 text-[10px] text-gray-400 dark:text-slate-500">يتم ضبط السنة تلقائيًا حسب الترم.</div>
                            @endif
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-500 dark:text-slate-400">الفصل الدراسي</label>
                            <select wire:model="templateTermId"
                                    class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                                <option value="">اختر الفصل</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                            <div class="mt-1 text-[10px] text-gray-400">حدد الترم الذي سيطبق عليه القالب.</div>
                            @error('templateTermId')
                                <span class="mt-1 text-[11px] text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex items-end">
                            <button wire:click="saveTemplate"
                                    class="w-full rounded-xl bg-gradient-to-l from-purple-600 to-purple-700 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-500/20 transition-all hover:shadow-lg hover:shadow-purple-500/30 hover:-translate-y-0.5">
                                <span wire:loading.remove wire:target="saveTemplate">حفظ القالب</span>
                                <span wire:loading wire:target="saveTemplate">جارٍ الحفظ...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($showApplyOptions)
                {{-- Apply baseline options --}}
                <div class="rounded-2xl border border-amber-200/40 bg-gradient-to-l from-amber-50/60 via-amber-50/40 to-transparent p-4 dark:border-amber-700/30 dark:from-amber-900/15 dark:via-amber-900/5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-amber-800 dark:text-amber-200">تطبيق الأساس بين الترمات</div>
                            <div class="mt-2 space-y-2">
                                <label class="flex items-center gap-2.5 rounded-lg bg-white/60 px-3 py-2 transition hover:bg-white dark:bg-slate-800/40 dark:hover:bg-slate-800/60">
                                    <input type="checkbox" wire:model="applyTemplateToAllTerms" class="rounded border-amber-300 text-amber-600 shadow-sm focus:ring-amber-400/30">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">طبّق القالب على جميع ترمات السنة (بدون استبدال الموجود)</span>
                                </label>
                                <label class="flex items-center gap-2.5 rounded-lg bg-white/60 px-3 py-2 transition hover:bg-white dark:bg-slate-800/40 dark:hover:bg-slate-800/60">
                                    <input type="checkbox" wire:model="applyTemplateToSubjects" class="rounded border-amber-300 text-amber-600 shadow-sm focus:ring-amber-400/30">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">اربط القالب بجميع مواد الصف المحدد</span>
                                </label>
                            </div>
                            <div class="mt-2 text-[10px] text-amber-600/70 dark:text-amber-400/60">
                                الربط يحتاج تحديد الصف من خطوة المواد.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($showCategoryTree)
                @if($activeTemplateId)
                    {{-- Category tree --}}
                    <div class="rounded-2xl border border-gray-200/40 bg-gray-50/40 p-5 dark:border-slate-700/30 dark:bg-slate-800/20">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                                </svg>
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">هيكلية الدرجات</h3>
                            </div>
                            <button wire:click="openCategoryForm"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-emerald-600 hover:shadow-md hover:-translate-y-0.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                إضافة فئة رئيسية
                            </button>
                        </div>

                        <div class="space-y-2">
                            @forelse($activeTemplate->categories as $category)
                                <x-grading-tree-item :category="$category" :level="0" />
                            @empty
                                <div class="py-8 text-center">
                                    <svg class="mx-auto h-10 w-10 text-gray-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z" />
                                    </svg>
                                    <div class="mt-2 text-sm text-gray-400">لا توجد فئات بعد</div>
                                    <div class="mt-1 text-xs text-gray-400">أضف الفئة الأولى لبناء هيكلة الدرجات</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-gray-300 py-16 text-center dark:border-slate-700">
                        <svg class="mx-auto h-12 w-12 text-gray-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <div class="mt-3 text-sm font-medium text-gray-500 dark:text-slate-400">اختر قالبًا من القائمة للبدء بضبط الفئات</div>
                        <div class="mt-1 text-xs text-gray-400">إن لم يكن لديك قالب بعد، ارجع لخطوة القوالب وأنشئ واحدًا</div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
