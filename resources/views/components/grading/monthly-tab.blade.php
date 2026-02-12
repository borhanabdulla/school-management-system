@props([
    'monthlyCategories' => [],
    'attendanceDeductAfter' => 0,
    'attendanceDeductPerAbsence' => 0,
    'attendanceMaxScore' => 0,
    'allowCustomCategories' => false,
    'grades' => [],
    'terms' => [],
    'subjectGradeId' => null,
    'subjectTermId' => null,
    'monthlyMappingSummary' => [],
    'monthlyMappingStatus' => [],
    'subjects' => [],
])

<div class="space-y-6" wire:init="loadMonthlySettings">
    {{-- Help hint --}}
    <x-grading.help-hint
        title="معلومة مهمة"
        message="بنود الدفتر الشهري تنطبق على جميع المواد. بعد تعريف البنود، اربط كل بند بفئة من القالب باستخدام المابينغ."
        variant="info"
    />

    {{-- ══════════════════════════════════════
         Step 1: Monthly Categories
    ══════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm dark:border-slate-700/50 dark:bg-slate-900/80">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40">
                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">1</span>
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-800 dark:text-white">بنود الدفتر الشهري</div>
                    <div class="text-[11px] text-gray-500 dark:text-slate-400">عرّف البنود الشهرية (تُطبق على جميع المواد)</div>
                </div>
            </div>
            <button wire:click="addMonthlyCategory"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-emerald-600 hover:shadow-md hover:-translate-y-0.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                إضافة بند
            </button>
        </div>

        @if(count($monthlyCategories) > 0)
            <div class="overflow-hidden rounded-xl border border-gray-200/60 dark:border-slate-700/50">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-800 text-sm">
                    <thead class="bg-gray-50/80 dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500">البند</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500">المفتاح</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500">الدرجة</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-800/60">
                        @foreach($monthlyCategories as $index => $cat)
                            <tr class="transition-colors hover:bg-gray-50/60 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <input type="text" wire:model="monthlyCategories.{{ $index }}.label"
                                               class="w-full rounded-lg border-gray-200 bg-transparent px-2.5 py-1.5 text-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:text-gray-200">
                                        <div class="text-[10px] text-gray-400">اسم البند الذي يراه المعلم.</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <input type="text" wire:model="monthlyCategories.{{ $index }}.key"
                                               class="w-full rounded-lg border-gray-200 bg-transparent px-2.5 py-1.5 text-xs font-mono text-gray-500 focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:text-slate-400">
                                        <div class="text-[10px] text-gray-400">مفتاح فريد لربط البنود.</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <input type="number" wire:model="monthlyCategories.{{ $index }}.max_score"
                                               class="w-20 rounded-lg border-gray-200 bg-transparent px-2.5 py-1.5 text-sm text-center focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:text-gray-200">
                                        <div class="text-[10px] text-gray-400">الحد الأعلى للبند.</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="removeMonthlyCategory({{ $index }})"
                                            class="rounded-lg p-1.5 text-rose-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/30">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                <button wire:click="saveMonthlyCategories"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-purple-600 to-purple-700 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-500/20 transition-all hover:shadow-lg hover:-translate-y-0.5">
                    <span wire:loading.remove wire:target="saveMonthlyCategories">حفظ البنود</span>
                    <span wire:loading wire:target="saveMonthlyCategories">جارٍ الحفظ...</span>
                </button>
            </div>
        @else
            <div class="py-8 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <div class="mt-2 text-sm text-gray-400">لا توجد بنود بعد</div>
                <div class="mt-1 text-xs text-gray-400">أضف البند الأول لبدء ضبط الدفتر الشهري</div>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════
         Attendance rules
    ══════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm dark:border-slate-700/50 dark:bg-slate-900/80">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-800 dark:text-white">قواعد المواظبة</div>
                <div class="text-[11px] text-gray-500 dark:text-slate-400">إعدادات خصم درجات الحضور</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 dark:text-slate-400">خصم بعد (أيام)</label>
                <input type="number" wire:model="attendanceDeductAfter"
                       class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                <div class="mt-1 text-[10px] text-gray-400">عدد أيام الغياب قبل بدء الخصم.</div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 dark:text-slate-400">درجة لكل غياب</label>
                <input type="number" step="0.01" wire:model="attendanceDeductPerAbsence"
                       class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                <div class="mt-1 text-[10px] text-gray-400">كم درجة تُخصم لكل غياب بعد الحد.</div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-gray-500 dark:text-slate-400">الدرجة القصوى للمواظبة</label>
                <input type="number" step="0.01" wire:model="attendanceMaxScore"
                       class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                <div class="mt-1 text-[10px] text-gray-400">الحد الأعلى الذي يمكن الحصول عليه في المواظبة.</div>
            </div>
        </div>

        <div class="mt-4">
            <label class="flex items-center gap-2.5 rounded-xl bg-gray-50 px-3.5 py-3 transition hover:bg-gray-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                <input type="checkbox" wire:model="allowCustomCategories" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-400/30">
                <span class="text-sm text-gray-700 dark:text-gray-300">السماح للمعلم بإضافة بنود شهرية خاصة</span>
            </label>
            <div class="mt-1 text-[10px] text-gray-400">إن تم إيقافها، لا يستطيع المعلم إضافة بنود جديدة في دفتره.</div>
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="saveMonthlySettings"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-purple-600 to-purple-700 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-500/20 transition-all hover:shadow-lg hover:-translate-y-0.5">
                <span wire:loading.remove wire:target="saveMonthlySettings">حفظ إعدادات المواظبة</span>
                <span wire:loading wire:target="saveMonthlySettings">جارٍ الحفظ...</span>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         Step 2: Monthly Mapping
    ══════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm dark:border-slate-700/50 dark:bg-slate-900/80">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                <span class="text-sm font-black text-blue-600 dark:text-blue-400">2</span>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-800 dark:text-white">ربط البنود بالقالب (مابينغ)</div>
                <div class="text-[11px] text-gray-500 dark:text-slate-400">حدد الصف والترم ثم اربط كل مادة ببنود القالب</div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-4 flex items-center gap-2">
            <select wire:model.live="subjectGradeId"
                    class="rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                <option value="">كل الصفوف</option>
                @foreach($grades as $grade)
                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="subjectTermId"
                    class="rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                <option value="">كل الترمات</option>
                @foreach($terms as $term)
                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Mapping status --}}
        @if(!empty($monthlyMappingSummary))
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($subjects as $subject)
                    @php
                        $sid = $subject->id;
                        $status = $monthlyMappingStatus[$sid] ?? 'unmapped';
                        $statusConfig = match($status) {
                            'complete' => ['color' => 'emerald', 'icon' => '✓', 'label' => 'مكتمل'],
                            'partial' => ['color' => 'amber', 'icon' => '◐', 'label' => 'جزئي'],
                            default => ['color' => 'gray', 'icon' => '○', 'label' => 'غير مربوط'],
                        };
                    @endphp
                    <button wire:click="openMonthlyMapping({{ $sid }})"
                            class="flex items-center gap-3 rounded-xl border border-{{ $statusConfig['color'] }}-200/60 bg-{{ $statusConfig['color'] }}-50/40 px-4 py-3 text-right transition-all duration-200 hover:shadow-md dark:border-{{ $statusConfig['color'] }}-700/30 dark:bg-{{ $statusConfig['color'] }}-900/10">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $statusConfig['color'] }}-100 text-sm font-bold text-{{ $statusConfig['color'] }}-600 dark:bg-{{ $statusConfig['color'] }}-800/40 dark:text-{{ $statusConfig['color'] }}-400">
                            {{ $statusConfig['icon'] }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $subject->name }}</div>
                            <div class="text-[11px] text-{{ $statusConfig['color'] }}-600 dark:text-{{ $statusConfig['color'] }}-400">{{ $statusConfig['label'] }}</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                @endforeach
            </div>
        @else
            <div class="py-8 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <div class="mt-2 text-sm text-gray-400">حدد الصف والترم لعرض حالة الربط</div>
            </div>
        @endif
    </div>
</div>
