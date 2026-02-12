@props(['category', 'level' => 0])

@php
    $levelColors = [
        0 => 'from-white to-gray-50/80 dark:from-slate-800/60 dark:to-slate-800/40 border-gray-200/60 dark:border-slate-700/50',
        1 => 'from-blue-50/40 to-blue-50/20 dark:from-blue-900/10 dark:to-slate-800/30 border-blue-200/50 dark:border-blue-800/30',
        2 => 'from-purple-50/30 to-purple-50/10 dark:from-purple-900/10 dark:to-slate-800/20 border-purple-200/40 dark:border-purple-800/25',
    ];
    $colorClass = $levelColors[$level] ?? $levelColors[2];
    $calcLabels = [
        'sum' => 'مجموع',
        'average' => 'متوسط',
        'weighted_average' => 'متوسط مرجح',
    ];
    $mappingLabels = [
        'manual' => 'يدوي',
        'monthly_average' => 'متوسط شهري',
        'attendance' => 'حضور',
        'homework' => 'واجبات',
        'final_exam' => 'اختبار نهائي',
    ];
    $calcLabel = $calcLabels[$category->calculation_type] ?? $category->calculation_type;
    $mappingType = $category->mapping_type ?? 'manual';
    $mappingLabel = $mappingLabels[$mappingType] ?? $mappingType;
@endphp

<div class="relative" style="margin-right: {{ $level * 24 }}px">
    {{-- Tree connector line --}}
    @if($level > 0)
        <div class="absolute -right-3 top-5 h-px w-3 bg-gray-200 dark:bg-slate-700"></div>
    @endif

    <div class="group rounded-xl border bg-gradient-to-l p-3.5 transition-all duration-200 hover:shadow-md {{ $colorClass }}">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Drag handle --}}
                <div class="flex-shrink-0 cursor-move rounded-lg p-1 text-gray-300 transition hover:bg-gray-100 hover:text-gray-500 dark:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </div>

                <div class="min-w-0">
                    {{-- Name + badges --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $category->name }}</span>

                        @if($category->is_locked)
                            <span class="inline-flex items-center gap-0.5 rounded-md bg-rose-100/80 px-1.5 py-0.5 text-[10px] font-semibold text-rose-600 dark:bg-rose-900/30 dark:text-rose-400" title="مقفل من الإدارة">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                مقفل
                            </span>
                        @endif
                        @if($category->is_final_exam)
                            <span class="inline-flex items-center gap-0.5 rounded-md bg-purple-100/80 px-1.5 py-0.5 text-[10px] font-semibold text-purple-600 dark:bg-purple-900/30 dark:text-purple-400" title="اختبار نهائي">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                                نهائي
                            </span>
                        @endif
                    </div>

                    {{-- Meta info --}}
                    <div class="mt-1 flex flex-wrap items-center gap-3 text-[11px]">
                        <span class="flex items-center gap-1 text-gray-500 dark:text-slate-400">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                            </svg>
                            <strong>{{ $category->weight }}</strong>
                        </span>
                        <span class="flex items-center gap-1 text-gray-500 dark:text-slate-400">
                            طريقة: {{ $calcLabel }}
                        </span>
                        <span class="flex items-center gap-1 text-gray-500 dark:text-slate-400">
                            مصدر: {{ $mappingLabel }}
                        </span>
                        @if($category->max_raw_score)
                            <span class="text-gray-400 dark:text-slate-500">(العظمى: {{ $category->max_raw_score }})</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                <button wire:click="openCategoryForm({{ $category->id }})"
                        class="rounded-lg p-1.5 text-emerald-500 transition hover:bg-emerald-50 dark:hover:bg-emerald-900/30"
                        title="إضافة فرع">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </button>
                <button wire:click="openCategoryForm({{ $category->parent_id ?? 'null' }}, {{ $category->id }})"
                        class="rounded-lg p-1.5 text-blue-500 transition hover:bg-blue-50 dark:hover:bg-blue-900/30"
                        title="تعديل">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </button>
                <button wire:click="deleteCategory({{ $category->id }})"
                        onclick="return confirm('هل أنت متأكد من حذف هذه الفئة وفروعها؟')"
                        class="rounded-lg p-1.5 text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-900/30"
                        title="حذف">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Recursive Children --}}
    @if($category->children->count() > 0)
        <div class="relative mt-1.5 space-y-1.5">
            {{-- Vertical connecting line --}}
            <div class="absolute -right-1 top-0 bottom-0 w-px bg-gray-200 dark:bg-slate-700" style="right: {{ $level * 24 + 23 }}px"></div>

            @foreach($category->children as $child)
                <x-grading-tree-item :category="$child" :level="$level + 1" />
            @endforeach
        </div>
    @endif
</div>
