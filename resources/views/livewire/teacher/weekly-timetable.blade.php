<div class="mt-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">الجدول الدراسي الأسبوعي</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">نظرة شاملة على حصصك الدراسية لهذا الأسبوع</p>
        </div>

        {{-- Pending Attendance Alert --}}
        @php
            $pendingCount = 0;
            if ($canTakeAttendance) {
                foreach($weeklySchedule as $dayIndex => $sessions) {
                    $date = $weekDates[$dayIndex] ?? null;
                    if (!$date) continue;
                    foreach($sessions as $session) {
                        $key = $date . '_' . $session->time_slot_id . '_' . $session->class_section_id;
                        $isTaken = $attendanceStatus[$key] ?? false;
                        $isPast = \Carbon\Carbon::parse($date)->isPast() && !\Carbon\Carbon::parse($date)->isToday();
                        $isToday = \Carbon\Carbon::parse($date)->isToday();
                        $startTimeValue = $session->timeSlot->start_time ?? null;
                        $startTime = $startTimeValue instanceof \DateTimeInterface
                            ? $startTimeValue->format('H:i')
                            : (is_string($startTimeValue) ? substr($startTimeValue, 0, 5) : '00:00');
                        $timePassed = $startTime !== '00:00' && \Carbon\Carbon::now()->format('H:i') >= $startTime;
                        if (($isPast || ($isToday && $timePassed)) && !$isTaken) {
                            $pendingCount++;
                        }
                    }
                }
            }
        @endphp

        @if($pendingCount > 0 && $canTakeAttendance)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-4 py-2 flex items-center gap-2 text-amber-700 dark:text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="font-bold text-sm">لديك {{ $pendingCount }} حصص تحتاج لرصد الغياب</span>
            </div>
        @endif
    </div>

    {{-- Grid Table View --}}
    @if($allTimeSlots->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[700px]">
                    {{-- Header: Day Names --}}
                    <thead>
                        <tr>
                            <th class="sticky right-0 z-10 bg-gray-50 dark:bg-gray-900 w-24 p-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                الفترة
                            </th>
                            @foreach($workingDays as $dayIndex)
                                @php
                                    $date = $weekDates[$dayIndex] ?? null;
                                    $isToday = $date ? \Carbon\Carbon::parse($date)->isToday() : false;
                                @endphp
                                <th class="p-3 text-center border-b border-gray-200 dark:border-gray-700 min-w-[140px] {{ $isToday ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'bg-gray-50 dark:bg-gray-900' }}">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-sm font-bold {{ $isToday ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $dayNames[$dayIndex] ?? '' }}
                                        </span>
                                        @if($date)
                                            <span class="text-xs {{ $isToday ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}">
                                                {{ \Carbon\Carbon::parse($date)->format('m/d') }}
                                            </span>
                                        @endif
                                        @if($isToday)
                                            <span class="text-[10px] bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-full font-bold">اليوم</span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    {{-- Body: Time Slots as Rows --}}
                    <tbody>
                        @foreach($allTimeSlots as $slot)
                            @php
                                $startTimeValue = $slot->start_time ?? null;
                                $slotStartTime = $startTimeValue instanceof \DateTimeInterface
                                    ? $startTimeValue->format('H:i')
                                    : (is_string($startTimeValue) ? substr($startTimeValue, 0, 5) : '00:00');
                                $endTimeValue = $slot->end_time ?? null;
                                $slotEndTime = $endTimeValue instanceof \DateTimeInterface
                                    ? $endTimeValue->format('H:i')
                                    : (is_string($endTimeValue) ? substr($endTimeValue, 0, 5) : '00:00');
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-700/50 last:border-b-0">
                                {{-- Time Slot Label --}}
                                <td class="sticky right-0 z-10 bg-white dark:bg-gray-800 p-2 text-center border-l border-gray-100 dark:border-gray-700">
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $slot->label }}</span>
                                        <span class="text-xs font-mono text-gray-400 dark:text-gray-500">{{ $slotStartTime }}</span>
                                        <span class="text-[10px] text-gray-300 dark:text-gray-600">{{ $slotEndTime }}</span>
                                    </div>
                                </td>

                                {{-- Day Cells --}}
                                @foreach($workingDays as $dayIndex)
                                    @php
                                        $session = $gridLookup[$dayIndex][$slot->order_index] ?? null;
                                        $date = $weekDates[$dayIndex] ?? null;
                                        $isToday = $date ? \Carbon\Carbon::parse($date)->isToday() : false;
                                        $isFutureDay = $date ? \Carbon\Carbon::parse($date)->isFuture() : false;

                                        // Status calculation
                                        $statusColor = 'gray';
                                        $statusLabel = '';
                                        $canMark = false;
                                        $isTaken = false;

                                        if ($session && $date) {
                                            $key = $date . '_' . $session->time_slot_id . '_' . $session->class_section_id;
                                            $isTaken = $attendanceStatus[$key] ?? false;
                                            $nowTime = \Carbon\Carbon::now()->format('H:i');

                                            if ($isFutureDay) {
                                                $statusColor = 'gray';
                                            } elseif ($isToday) {
                                                if ($nowTime < $slotStartTime) {
                                                    $statusColor = 'gray';
                                                } else {
                                                    $canMark = true;
                                                    $statusColor = $isTaken ? 'green' : 'amber';
                                                }
                                            } else {
                                                $canMark = true;
                                                $statusColor = $isTaken ? 'green' : 'red';
                                            }
                                        }
                                    @endphp

                                    <td class="p-1.5 {{ $isToday ? 'bg-indigo-50/30 dark:bg-indigo-900/10' : '' }}">
                                        @if($session)
                                            <div class="relative rounded-xl p-3 h-full min-h-[80px] border transition-all
                                                {{ $statusColor === 'green' ? 'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800' :
                                                   ($statusColor === 'amber' ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800' :
                                                   ($statusColor === 'red' ? 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800' :
                                                   'bg-gray-50 dark:bg-gray-700/30 border-gray-200 dark:border-gray-700')) }}">

                                                {{-- Status Indicator --}}
                                                <div class="absolute top-1.5 left-1.5">
                                                    <span class="w-2 h-2 rounded-full block
                                                        {{ $statusColor === 'green' ? 'bg-emerald-500' :
                                                           ($statusColor === 'amber' ? 'bg-amber-500 animate-pulse' :
                                                           ($statusColor === 'red' ? 'bg-red-500' : 'bg-gray-300 dark:bg-gray-600')) }}">
                                                    </span>
                                                </div>

                                                {{-- Subject Name --}}
                                                <h4 class="font-bold text-sm text-gray-900 dark:text-white leading-tight mb-1 pl-4">
                                                    {{ $session->courseOffering?->subject?->name }}
                                                </h4>

                                                {{-- Class Section --}}
                                                <span class="inline-block text-[11px] font-medium px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 mb-2">
                                                    {{ $session->classSection?->full_name }}
                                                </span>

                                                {{-- Action Button --}}
                                                @if($canMark && $canTakeAttendance)
                                                    <a href="{{ route('attendance.take', $session->id) }}" wire:navigate
                                                       class="block text-center text-[11px] font-bold rounded-lg py-1 transition-colors
                                                        {{ $isTaken
                                                            ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-900/50'
                                                            : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm' }}">
                                                        {{ $isTaken ? '✓ تم' : 'رصد' }}
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            {{-- Empty Cell --}}
                                            <div class="rounded-xl h-full min-h-[80px] border-2 border-dashed border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/30"></div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>تم الرصد</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>بانتظار الرصد</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    <span>متأخر</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                    <span>قادمة / مستقبلية</span>
                </div>
            </div>
        </div>

        {{-- Mobile: Card View (shown on small screens) --}}
        <div class="block sm:hidden mt-4 space-y-4">
            @foreach($workingDays as $dayIndex)
                @php
                    $date = $weekDates[$dayIndex] ?? null;
                    $isToday = $date ? \Carbon\Carbon::parse($date)->isToday() : false;
                    $daySessions = $weeklySchedule[$dayIndex] ?? collect();
                @endphp

                @if($daySessions->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-xl border {{ $isToday ? 'border-indigo-300 dark:border-indigo-700' : 'border-gray-200 dark:border-gray-700' }} overflow-hidden">
                        <div class="px-4 py-2 {{ $isToday ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'bg-gray-50 dark:bg-gray-900/50' }} flex items-center justify-between">
                            <span class="font-bold text-sm {{ $isToday ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $dayNames[$dayIndex] ?? '' }}
                            </span>
                            @if($isToday)
                                <span class="text-[10px] bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-full font-bold">اليوم</span>
                            @endif
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($daySessions as $session)
                                @php
                                    $startTimeValue = $session->timeSlot->start_time ?? null;
                                    $startTime = $startTimeValue instanceof \DateTimeInterface
                                        ? $startTimeValue->format('H:i')
                                        : (is_string($startTimeValue) ? substr($startTimeValue, 0, 5) : '00:00');
                                @endphp
                                <div class="px-4 py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-mono font-bold text-gray-400 w-10">{{ $startTime }}</span>
                                        <div>
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ $session->courseOffering?->subject?->name }}</h4>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $session->classSection?->full_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center">
            <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">لا يوجد جدول دراسي</h3>
            <p class="text-gray-500 dark:text-gray-400">لم يتم تعيين أي حصص لك في الجدول الأسبوعي بعد.</p>
        </div>
    @endif
</div>
