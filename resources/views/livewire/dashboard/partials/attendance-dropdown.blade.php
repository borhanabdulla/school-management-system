<div id="attendance-dropdown" x-data="{ open: @entangle('attendanceDropdownOpen') }" x-cloak class="mb-10"
    x-effect="if (open) { $nextTick(() => { $el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }); }">
    <div x-show="open"
        class="modern-card-elevated p-6 border border-emerald-500/20 bg-emerald-500/5"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تفاصيل الحضور اليومي</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $attendanceDropdownDate ?? now()->toDateString() }}</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <a href="{{ route('teacher.attendance.report') }}" class="text-indigo-500 hover:text-indigo-400">تقرير المعلمين</a>
                <a href="{{ route('class-sections.index') }}" class="text-indigo-500 hover:text-indigo-400">قائمة الشعب</a>
                <button type="button" class="text-slate-500 hover:text-slate-700 dark:text-slate-300" @click="$wire.closeAttendanceDropdown()">
                    إغلاق
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            @foreach ($attendanceDropdownData['items'] ?? [] as $item)
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full {{ $dotMap[$item['color']] ?? 'bg-slate-400' }}"></span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $item['label'] }}</span>
                        </div>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item['total'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">أضعف الشعب بالحضور</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @forelse ($attendanceDropdownData['classes'] ?? [] as $row)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-900 dark:text-white">{{ $row['label'] }}</p>
                            <span class="text-xs font-semibold text-amber-500">{{ $row['rate'] }}%</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">حضور {{ $row['present'] }} من {{ $row['total'] }}</p>
                    </div>
                @empty
                    <div class="text-xs text-gray-500 dark:text-slate-400">لا تتوفر تفاصيل إضافية لهذا اليوم.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
