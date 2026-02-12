@props([
    'show' => false,
    'mappingSubjectName' => '',
    'mappingCategories' => [],
    'mappingTemplateCategories' => [],
    'aggregationRuleOptions' => [],
    'missingMonthsPolicyOptions' => [],
])

@if($show)
    <div class="fixed inset-0 z-50"
         x-data="{ open: true }"
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeMonthlyMapping"></div>

        {{-- Slide-over panel --}}
        <div class="absolute right-0 top-0 h-full w-full max-w-4xl overflow-y-auto bg-white shadow-2xl dark:bg-slate-900"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0">
            {{-- Header --}}
            <div class="sticky top-0 z-10 border-b border-gray-200/60 bg-white/95 px-6 py-5 backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/95">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">مابينغ الدفتر الشهري</div>
                            <div class="text-sm text-gray-500 dark:text-slate-400">{{ $mappingSubjectName ?: 'المادة' }}</div>
                        </div>
                    </div>
                    <button wire:click="closeMonthlyMapping"
                            class="rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6 space-y-5">
                @if(empty($mappingCategories))
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <div class="mt-3 text-sm text-gray-400">لا توجد بنود شهرية للعرض.</div>
                    </div>
                @elseif(empty($mappingTemplateCategories))
                    <x-grading.help-hint
                        title="تنبيه"
                        message="لا توجد فئات قالب متاحة لهذه المادة. تأكد من ربط المادة بقالب أولاً."
                        variant="warning"
                        :dismissible="false"
                    />
                @else
                    {{-- Mapping table --}}
                    <div class="overflow-hidden rounded-xl border border-gray-200/60 dark:border-slate-700/50">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-800 text-sm">
                            <thead class="bg-gray-50/80 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500">البند</th>
                                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500">فئة القالب</th>
                                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500">قاعدة التجميع</th>
                                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500">سياسة الأشهر الناقصة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-slate-800/60">
                                @foreach($mappingCategories as $category)
                                    @php($key = $category['key'] ?? '')
                                    <tr class="transition-colors hover:bg-gray-50/60 dark:hover:bg-slate-800/30">
                                        <td class="px-5 py-3.5">
                                            <div class="font-semibold text-gray-700 dark:text-gray-200">{{ $category['label'] ?? '' }}</div>
                                            <div class="mt-0.5 text-[10px] font-mono text-gray-400 dark:text-slate-500">{{ $key }}</div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <select wire:model="monthlyCategoryMappings.{{ $key }}.template_category_id"
                                                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                                                <option value="">-- اختر فئة --</option>
                                                @foreach($mappingTemplateCategories as $option)
                                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <select wire:model="monthlyCategoryMappings.{{ $key }}.aggregation_rule"
                                                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                                                @foreach($aggregationRuleOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <select wire:model="monthlyCategoryMappings.{{ $key }}.missing_months_policy"
                                                    class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
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

                    {{-- Footer actions --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-slate-800">
                        <button wire:click="closeMonthlyMapping"
                                class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300 dark:hover:bg-slate-800">
                            إلغاء
                        </button>
                        <button wire:click="saveMonthlyMappings"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-blue-600 to-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-500/20 transition-all hover:shadow-lg hover:-translate-y-0.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            حفظ المابينغ
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
