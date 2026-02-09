<div class="modern-card-elevated p-6 mb-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">خريطة الحضور</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400">آخر {{ $range }} يوم — قراءة سريعة لنبض الحضور</p>
        </div>
        <div class="text-xs text-gray-500 dark:text-slate-400">
            {{ $attendanceHeatmap['start'] ?? '' }} إلى {{ $attendanceHeatmap['end'] ?? '' }}
        </div>
    </div>

    <div class="grid grid-cols-7 gap-2 text-[11px] text-center text-gray-500 dark:text-slate-400 mb-3">
        @foreach (($attendanceHeatmap['labels'] ?? []) as $label)
            <div>{{ $label }}</div>
        @endforeach
    </div>

    <div class="grid gap-2">
        @foreach (($attendanceHeatmap['weeks'] ?? []) as $week)
            <div class="grid grid-cols-7 gap-2">
                @foreach ($week as $day)
                    <button type="button"
                        class="relative group rounded-lg border {{ $day['tone'] ?? 'border-slate-200' }} h-12 flex flex-col items-center justify-center text-[11px] transition hover:scale-[1.03]"
                        wire:click="openAttendanceDropdown('{{ $day['date'] }}')">
                        <div class="pointer-events-none absolute -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900/95 px-2 py-1 text-[10px] text-white opacity-0 transition group-hover:opacity-100">
                            {{ $day['date'] }} | حضور {{ $day['present'] }} من {{ $day['total'] }} ({{ $day['rate'] }}%)
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $day['day'] }}</span>
                        <span class="text-[10px] text-gray-500 dark:text-slate-400">{{ $day['rate'] }}%</span>
                        @if ($day['is_holiday'])
                            <span class="absolute top-1 left-1 h-2 w-2 rounded-full bg-sky-400"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-slate-400">
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-emerald-500/30 border border-emerald-400/40"></span>
            95%+
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-emerald-400/25 border border-emerald-300/40"></span>
            90-94%
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-amber-400/25 border border-amber-300/40"></span>
            80-89%
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-orange-400/25 border border-orange-300/40"></span>
            70-79%
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-red-500/25 border border-red-400/40"></span>
            أقل من 70%
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-sky-500/20 border border-sky-500/30"></span>
            عطلة
        </div>
    </div>
</div>
