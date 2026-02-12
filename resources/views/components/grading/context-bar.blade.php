@props([
    'terms' => [],
    'grades' => [],
    'academicYears' => [],
    'selectedTermId' => null,
    'selectedGradeId' => null,
    'missingCount' => 0,
    'invalidCount' => 0,
    'isClean' => true,
    'healthChecked' => false,
])

@php
    $selectedTerm = collect($terms)->firstWhere('id', $selectedTermId);
    $selectedGrade = collect($grades)->firstWhere('id', $selectedGradeId);
    $selectedYear = null;

    if ($selectedTerm && !empty($academicYears)) {
        $selectedYear = collect($academicYears)->firstWhere('id', $selectedTerm->academic_year_id);
    }

    $yearStatusMap = [
        'active' => 'نشطة',
        'closed' => 'مغلقة',
        'archived' => 'مؤرشفة',
        'pending' => 'قيد التجهيز',
    ];
    $termStatusMap = [
        'active' => 'نشط',
        'completed' => 'مكتمل',
        'pending' => 'قيد التجهيز',
    ];

    $yearStatus = data_get($selectedYear, 'status');
    $termStatus = data_get($selectedTerm, 'status');
    $yearStatusValue = $yearStatus instanceof \BackedEnum
        ? $yearStatus->value
        : ($yearStatus instanceof \UnitEnum ? $yearStatus->name : $yearStatus);
    $termStatusValue = $termStatus instanceof \BackedEnum
        ? $termStatus->value
        : ($termStatus instanceof \UnitEnum ? $termStatus->name : $termStatus);
    $yearStatusLabel = $yearStatusMap[$yearStatusValue] ?? ($yearStatusValue ?: 'غير محدد');
    $termStatusLabel = $termStatusMap[$termStatusValue] ?? ($termStatusValue ?: 'غير محدد');

    $isReadOnly = in_array($yearStatusValue, ['closed', 'archived'], true) || $termStatusValue === 'completed';
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200/60 bg-white/95 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/80">
    {{-- Read-only banner --}}
    @if($isReadOnly)
        <div class="flex items-center gap-2 bg-gradient-to-l from-rose-500/10 via-rose-500/5 to-transparent px-5 py-2.5 text-sm dark:from-rose-900/30 dark:via-rose-900/10">
            <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <span class="font-semibold text-rose-700 dark:text-rose-400">وضع القراءة فقط</span>
            <span class="text-rose-600/70 dark:text-rose-400/70">— لا يمكن تعديل الإعدادات (السنة مغلقة أو الترم مكتمل)</span>
        </div>
    @endif

    <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
        {{-- Context info chips --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Year --}}
            <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-3.5 py-2 dark:bg-slate-800/60">
                <svg class="h-4 w-4 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <div>
                    <div class="text-[10px] font-medium text-gray-400 dark:text-slate-500">السنة</div>
                    <div class="text-xs font-bold text-gray-700 dark:text-slate-200">{{ $selectedYear->name ?? 'غير محدد' }}</div>
                </div>
                <span class="rounded-full bg-blue-100/80 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    {{ $yearStatusLabel }}
                </span>
            </div>

            {{-- Term --}}
            <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-3.5 py-2 dark:bg-slate-800/60">
                <svg class="h-4 w-4 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div>
                    <div class="text-[10px] font-medium text-gray-400 dark:text-slate-500">الترم</div>
                    <div class="text-xs font-bold text-gray-700 dark:text-slate-200">{{ $selectedTerm->name ?? 'غير محدد' }}</div>
                </div>
                <span class="rounded-full bg-blue-100/80 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    {{ $termStatusLabel }}
                </span>
            </div>

            {{-- Grade --}}
            <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-3.5 py-2 dark:bg-slate-800/60">
                <svg class="h-4 w-4 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                </svg>
                <div>
                    <div class="text-[10px] font-medium text-gray-400 dark:text-slate-500">الصف</div>
                    <div class="text-xs font-bold text-gray-700 dark:text-slate-200">{{ $selectedGrade->name ?? 'غير محدد' }}</div>
                </div>
            </div>
        </div>

        {{-- Health status + action --}}
        <div class="flex items-center gap-3">
            @if(! $healthChecked)
                <div class="flex items-center gap-1.5 rounded-xl border border-slate-200/60 bg-slate-50/80 px-3 py-2 text-xs font-semibold text-slate-600 dark:border-slate-700/40 dark:bg-slate-900/40 dark:text-slate-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    لم يتم الفحص بعد
                </div>
            @elseif(! $isClean)
                <div class="flex items-center gap-2 rounded-xl border border-amber-200/60 bg-amber-50/80 px-3 py-2 dark:border-amber-700/40 dark:bg-amber-900/20">
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300">
                        <span class="flex h-2 w-2 items-center justify-center">
                            <span class="absolute h-2 w-2 animate-ping rounded-full bg-amber-400/60"></span>
                            <span class="relative h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        </span>
                        Missing: {{ (int) $missingCount }}
                    </div>
                    <div class="h-3 w-px bg-amber-300/60 dark:bg-amber-600/40"></div>
                    <div class="text-xs font-semibold text-rose-700 dark:text-rose-300">
                        Invalid: {{ (int) $invalidCount }}
                    </div>
                </div>
            @else
                <div class="flex items-center gap-1.5 rounded-xl border border-emerald-200/60 bg-emerald-50/80 px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-emerald-700/40 dark:bg-emerald-900/20 dark:text-emerald-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    جاهز
                </div>
            @endif

            <button
                type="button"
                wire:click="$dispatch('runGradingHealthCheckRequested')"
                class="group inline-flex items-center gap-1.5 rounded-xl border border-purple-200/60 bg-purple-50/80 px-3 py-2 text-xs font-semibold text-purple-700 transition-all hover:bg-purple-100 hover:shadow-sm dark:border-purple-700/40 dark:bg-purple-900/20 dark:text-purple-300 dark:hover:bg-purple-900/40"
            >
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                </svg>
                فحص الصحة
            </button>
        </div>
    </div>
</div>
