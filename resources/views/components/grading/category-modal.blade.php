@props([
    'editingCategoryId' => 0,
    'categoryPassRequired' => false,
    'categoryCalculationType' => 'sum',
    'categoryMappingType' => 'manual',
])

<x-dialog-modal wire:model="showCategoryForm">
    <x-slot name="title">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $editingCategoryId ? 'bg-blue-100 dark:bg-blue-900/40' : 'bg-emerald-100 dark:bg-emerald-900/40' }}">
                @if($editingCategoryId)
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                @endif
            </div>
            <div>
                <div class="text-base font-bold text-gray-900 dark:text-white">
                    {{ $editingCategoryId ? 'تعديل الفئة' : 'إضافة فئة جديدة' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-slate-400">حدد خصائص الفئة وأوزانها</div>
            </div>
        </div>
    </x-slot>

    <x-slot name="content">
        <div class="space-y-6">
            {{-- ═══ Section 1: Basics ═══ --}}
            <div>
                <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    الأساسيات
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">اسم الفئة</label>
                        <input type="text" wire:model="categoryName"
                               placeholder="مثال: أعمال السنة، الاختبار النصفي، المشاركة..."
                               class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800">
                        <div class="mt-1 text-[10px] text-gray-400">يظهر للمعلم والطالب في التقارير.</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">الوزن النسبي</label>
                            <input type="number" step="0.01" wire:model="categoryWeight"
                                   placeholder="مثال: 20"
                                   class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800">
                            <div class="mt-1 text-[10px] text-gray-400">نسبة الفئة من الدرجة النهائية للمادة.</div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">الدرجة العظمى (الخام)</label>
                            <input type="number" step="0.01" wire:model="categoryMaxRawScore"
                                   placeholder="مثال: 100"
                                   class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800">
                            <span class="mt-1 text-[10px] text-gray-400">الحد الأعلى للدرجة الخام. مطلوب للفئة النهائية.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-px bg-gray-100 dark:bg-slate-800"></div>

            {{-- ═══ Section 2: Calculation ═══ --}}
            <div>
                <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" />
                    </svg>
                    طريقة الحساب ومصدر الدرجة
                </div>
                @php
                    $calcLabels = [
                        'sum' => 'مجموع',
                        'average' => 'متوسط',
                        'weighted_average' => 'متوسط مرجح',
                    ];
                    $mappingLabels = [
                        'manual' => 'يدوي',
                        'attendance' => 'حضور',
                        'homework' => 'واجبات',
                        'final_exam' => 'اختبار نهائي',
                        'monthly_average' => 'متوسط شهري',
                    ];
                    $calcLabel = $calcLabels[$categoryCalculationType] ?? $categoryCalculationType;
                    $mappingLabel = $mappingLabels[$categoryMappingType] ?? $categoryMappingType;
                    $unsupportedCalc = $categoryCalculationType !== 'sum';
                    $unsupportedMapping = ! in_array($categoryMappingType, ['manual', 'attendance', 'homework'], true);
                @endphp

                <div class="rounded-xl border border-gray-200/70 bg-gray-50/60 p-4 text-xs text-gray-600 dark:border-slate-700/60 dark:bg-slate-800/40 dark:text-slate-300">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-slate-400">طريقة الحساب (قياسي)</div>
                            <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">مجموع</div>
                            <div class="text-[11px] text-gray-500 dark:text-slate-400">هذا هو السلوك الفعلي للنظام حالياً.</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-slate-400">مصدر الدرجة</div>
                            <select wire:model="categoryMappingType"
                                    class="mt-2 w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800">
                                <option value="manual">يدوي</option>
                                <option value="attendance">حضور (آلي)</option>
                                <option value="homework">واجبات (آلي)</option>
                            </select>
                            <p class="mt-1 text-[10px] text-gray-400">التغيير هنا يؤثر فقط على المزامنة الآلية.</p>
                        </div>
                    </div>
                </div>

                @if($unsupportedCalc || $unsupportedMapping)
                    <div class="mt-3 rounded-xl border border-amber-200/70 bg-amber-50/70 p-3 text-[11px] text-amber-800 dark:border-amber-700/40 dark:bg-amber-900/20 dark:text-amber-200">
                        <div class="font-semibold">تنبيه: هذه الفئة محفوظة بقيم غير مستخدمة حالياً</div>
                        <div class="mt-1">طريقة الحساب: {{ $calcLabel }} — مصدر الدرجة: {{ $mappingLabel }}</div>
                        <div class="mt-2">
                            <button type="button" wire:click="resetCategoryDefaults"
                                    class="rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-[11px] font-semibold text-amber-700 hover:bg-amber-50 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                                إعادة ضبط القيم القياسية
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="h-px bg-gray-100 dark:bg-slate-800"></div>

            {{-- ═══ Section 3: Passing rules ═══ --}}
            <div>
                <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    قواعد النجاح
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <label class="group relative flex cursor-pointer items-start gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                        <div class="flex h-5 items-center">
                            <input type="checkbox" wire:model="categoryPassRequired" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-400/30">
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">يتطلب نجاح</span>
                            <span class="block text-[10px] text-gray-500 dark:text-slate-400">يجب على الطالب تحقيق درجة نجاح في هذه الفئة تحديداً لاجتياز المادة.</span>
                        </div>
                    </label>
                    <div class="{{ !$categoryPassRequired ? 'opacity-50' : '' }}">
                        <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">حد النجاح</label>
                        <input type="number" step="0.01" wire:model="categoryPassThreshold"
                               placeholder="مثال: 50"
                               class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 disabled:bg-gray-100"
                               @if(!$categoryPassRequired) disabled @endif>
                         <p class="mt-1 text-[10px] text-gray-400">أقل نسبة مطلوبة داخل هذه الفئة.</p>
                    </div>
                </div>
            </div>

            <div class="h-px bg-gray-100 dark:bg-slate-800"></div>

            {{-- ═══ Section 4: Permissions ═══ --}}
            <div>
                <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    الأذونات والخصائص
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <!-- Locked -->
                    <label class="group flex cursor-pointer items-start gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                        <div class="flex h-5 items-center">
                            <input type="checkbox" wire:model="categoryIsLocked" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-400/30">
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">قفل الأوزان</span>
                            <span class="block text-[10px] text-gray-500 dark:text-slate-400">منع المعلم من تعديل أوزان البنود.</span>
                        </div>
                    </label>

                    <!-- Readonly -->
                    <label class="group flex cursor-pointer items-start gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                        <div class="flex h-5 items-center">
                            <input type="checkbox" wire:model="categoryIsReadonly" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-400/30">
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">للقراءة فقط</span>
                            <span class="block text-[10px] text-gray-500 dark:text-slate-400">للعرض فقط، لا يمكن للمعلم رصد درجات.</span>
                        </div>
                    </label>

                    <!-- Final Exam -->
                    <label class="group flex cursor-pointer items-start gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                        <div class="flex h-5 items-center">
                            <input type="checkbox" wire:model="categoryIsFinalExam" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-400/30">
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">اختبار نهائي</span>
                            <span class="block text-[10px] text-gray-500 dark:text-slate-400">تعامل كاختبار نهائي في الشهادات.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="footer">
        <div class="flex items-center justify-end gap-3">
            <x-secondary-button wire:click="$set('showCategoryForm', false)" wire:loading.attr="disabled"
                                class="rounded-xl border-gray-200 px-4 py-2.5 text-sm">
                إلغاء
            </x-secondary-button>

            <x-button class="rounded-xl bg-gradient-to-l from-purple-600 to-purple-700 px-5 py-2.5 text-sm font-bold shadow-md shadow-purple-500/20 hover:shadow-lg"
                      wire:click="saveCategory"
                      wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveCategory">حفظ الفئة</span>
                <span wire:loading wire:target="saveCategory">جارٍ الحفظ...</span>
            </x-button>
        </div>
    </x-slot>
</x-dialog-modal>
