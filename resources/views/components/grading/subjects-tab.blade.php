@props([
    'subjects' => [],
    'grades' => [],
    'terms' => [],
    'templates' => [],
    'subjectGradeId' => null,
    'subjectTermId' => null,
    'subjectConfigs' => [],
    'subjectSearch' => '',
    'bulkTemplateId' => null,
    'gradingHealthIsClean' => true,
    'gradingHealthMissingCount' => 0,
    'gradingHealthInvalidCount' => 0,
])

@php
    $subjectCollection = collect($subjects);
    $searchTerm = trim((string) $subjectSearch);
    $searchTerm = $searchTerm !== '' ? $searchTerm : null;
    $configMap = collect($subjectConfigs);

    $totalSubjects = $subjectCollection->count();
    $linkedCount = $configMap->filter(fn($c) => !empty($c['template_id']))->count();
    $unlinkCount = $totalSubjects - $linkedCount;
    $linkPercentage = $totalSubjects > 0 ? round(($linkedCount / $totalSubjects) * 100) : 0;
@endphp

<div class="space-y-6">
    {{-- Help hint --}}
    <x-grading.help-hint
        title="لماذا ربط المواد مهم؟"
        message="كل مادة بدون قالب تظهر كـ Missing في فحص الصحة وتمنع الإغلاق. اربط كل المواد بقوالب مناسبة."
        variant="warning"
    />

    {{-- Quick-apply card --}}
    <div class="rounded-2xl border border-purple-200/40 bg-gradient-to-l from-purple-50/60 via-purple-50/40 to-transparent p-5 dark:border-purple-700/30 dark:from-purple-900/15 dark:via-purple-900/5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/40">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-purple-800 dark:text-purple-200">تطبيق سريع</div>
                    <div class="mt-0.5 text-xs text-purple-600/70 dark:text-purple-400/60">اربط جميع المواد غير المربوطة بقالب واحد</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <select wire:model="bulkTemplateId"
                        class="rounded-xl border-purple-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                    <option value="">اختر قالب</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                <div class="text-[10px] text-purple-600/70 dark:text-purple-400/60">يطبّق على المواد غير المربوطة فقط.</div>
                <button wire:click="applyBulkTemplate"
                        @disabled(!$bulkTemplateId)
                        class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-purple-700 hover:shadow-md disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    تطبيق
                </button>
            </div>
        </div>
    </div>

    {{-- Linking progress bar --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-4 dark:border-slate-700/50 dark:bg-slate-900/80">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-200">نسبة الربط</span>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    ✓ {{ $linkedCount }}
                </span>
                @if($unlinkCount > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                        ✗ {{ $unlinkCount }}
                    </span>
                @endif
                <span class="font-bold {{ $linkPercentage === 100 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $linkPercentage }}%</span>
            </div>
        </div>
        <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-slate-800">
            <div class="h-2 rounded-full bg-gradient-to-l {{ $linkPercentage === 100 ? 'from-emerald-400 to-emerald-600' : 'from-amber-400 to-amber-600' }} transition-all duration-700" style="width: {{ $linkPercentage }}%"></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="subjectSearch"
                   placeholder="بحث عن مادة..."
                   class="w-full rounded-xl border-gray-200 bg-white py-2.5 pr-10 pl-4 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
        </div>
        <div class="flex items-center gap-2">
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
    </div>

    {{-- Subjects grid --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @php
            $filteredSubjects = $subjectCollection;
            if ($searchTerm) {
                $filteredSubjects = $filteredSubjects->filter(fn($s) => str_contains($s->name ?? '', $searchTerm));
            }
        @endphp

        @forelse($filteredSubjects as $subject)
            @php
                $subjectId = $subject->id;
                $config = $configMap[$subjectId] ?? null;
                $hasTemplate = !empty($config['template_id']);
                $templateName = $hasTemplate ? (collect($templates)->firstWhere('id', $config['template_id'])?->name ?? 'غير معروف') : null;
            @endphp
            <div class="group rounded-2xl border p-4 transition-all duration-200 hover:shadow-md
                {{ $hasTemplate
                    ? 'border-emerald-200/50 bg-emerald-50/30 hover:border-emerald-300/60 dark:border-emerald-700/30 dark:bg-emerald-900/10'
                    : 'border-rose-200/50 bg-rose-50/20 hover:border-rose-300/60 dark:border-rose-700/30 dark:bg-rose-900/10' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $hasTemplate ? 'bg-emerald-100 dark:bg-emerald-800/40' : 'bg-rose-100 dark:bg-rose-800/40' }}">
                            @if($hasTemplate)
                                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                </svg>
                            @else
                                <svg class="h-4 w-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.181 8.68a4.503 4.503 0 0 1 1.903 6.405m-9.768-2.782L3.56 14.06a4.5 4.5 0 0 0 6.364 6.364l3.756-3.756m0 0 .177-.177m0 0 4.413-4.414a4.5 4.5 0 0 0-6.364-6.364l-4.59 4.59m9.778 4.188L13.18 8.68" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $subject->name }}</div>
                            @if($hasTemplate)
                                <div class="mt-0.5 text-[11px] text-emerald-600 dark:text-emerald-400">{{ $templateName }}</div>
                            @else
                                <div class="mt-0.5 text-[11px] text-rose-600 dark:text-rose-400">غير مربوط</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <select wire:change="updateSubjectConfig({{ $subjectId }}, $event.target.value)"
                            class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                        <option value="">-- بدون قالب --</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected($hasTemplate && $config['template_id'] == $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-1 text-[10px] text-gray-400">اختيار القالب يحدد طريقة احتساب المادة.</div>
                </div>

                @if($hasTemplate)
                    <div class="mt-2 flex items-center justify-between">
                        <button wire:click="recomputeSubjectCoursework({{ $subjectId }})"
                                class="text-[11px] font-semibold text-purple-600 transition hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">
                            إعادة التجميع
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full py-8 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <div class="mt-2 text-sm text-gray-400">لا توجد مواد مطابقة</div>
            </div>
        @endforelse
    </div>
</div>
