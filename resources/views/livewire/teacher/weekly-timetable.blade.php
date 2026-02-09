<div class="mt-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">الجدول الدراسي الأسبوعي</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">نظرة شاملة على حصصك الدراسية لهذا الأسبوع</p>
        </div>
        
        {{-- Alerts Summary --}}
        @php
            $pendingCount = 0;
            foreach($weeklySchedule as $dayIndex => $sessions) {
                $date = $weekDates[$dayIndex] ?? null;
                if (!$date) continue;
                
                foreach($sessions as $session) {
                    $key = $date . '_' . $session->time_slot_id . '_' . $session->class_section_id;
                    $isTaken = $attendanceStatus[$key] ?? false;
                    
                    // Logic for pending: Day passed OR (Today AND Time passed) AND Not Taken
                    $isPast = \Carbon\Carbon::parse($date)->isPast() && !\Carbon\Carbon::parse($date)->isToday();
                    $isToday = \Carbon\Carbon::parse($date)->isToday();
                    $timePassed = $session->timeSlot->start_time && \Carbon\Carbon::now()->format('H:i') >= $session->timeSlot->start_time->format('H:i');
                    
                    if (($isPast || ($isToday && $timePassed)) && !$isTaken) {
                        $pendingCount++;
                    }
                }
            }
        @endphp

        @if($pendingCount > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 flex items-center gap-2 text-amber-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="font-bold text-sm">لديك {{ $pendingCount }} حصص تحتاج لرصد الغياب</span>
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        @forelse($weeklySchedule as $dayIndex => $sessions)
            @php
                $date = $weekDates[$dayIndex] ?? null;
                $isToday = $date ? \Carbon\Carbon::parse($date)->isToday() : false;
                $isFutureDay = $date ? \Carbon\Carbon::parse($date)->isFuture() : false;
            @endphp
            <div class="border-b border-gray-100 dark:border-gray-700 last:border-b-0 {{ $isToday ? 'bg-indigo-50/30' : '' }}">
                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3 font-bold text-gray-700 dark:text-gray-300 text-sm flex items-center gap-2 justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $isToday ? 'bg-green-500' : 'bg-indigo-500' }}"></span>
                        {{ $dayNames[$dayIndex] ?? 'يوم ' . $dayIndex }}
                        <span class="text-xs font-normal text-gray-400">({{ $date }})</span>
                    </div>
                    @if($isToday)
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">اليوم</span>
                    @endif
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($sessions as $session)
                        @php
                            $key = $date . '_' . $session->time_slot_id . '_' . $session->class_section_id;
                            $isTaken = $attendanceStatus[$key] ?? false;
                            
                            // Time Logic
                            $startTime = $session->timeSlot->start_time ? $session->timeSlot->start_time->format('H:i') : '00:00';
                            $nowTime = \Carbon\Carbon::now()->format('H:i');
                            
                            $canMark = false;
                            $statusLabel = 'قادمة';
                            $statusColor = 'gray';

                            if ($isFutureDay) {
                                $statusLabel = 'مستقبلية';
                                $statusColor = 'gray';
                            } elseif ($isToday) {
                                if ($nowTime < $startTime) {
                                    $statusLabel = 'لم تبدأ بعد';
                                    $statusColor = 'gray';
                                } else {
                                    $canMark = true;
                                    $statusLabel = $isTaken ? 'تم الرصد' : 'بانتظار الرصد';
                                    $statusColor = $isTaken ? 'green' : 'amber';
                                }
                            } else { // Past Day
                                $canMark = true;
                                $statusLabel = $isTaken ? 'تم الرصد' : 'متأخر';
                                $statusColor = $isTaken ? 'green' : 'red';
                            }
                        @endphp

                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                            <div class="flex items-center gap-4">
                                {{-- Time Box --}}
                                <div class="flex flex-col items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700/50 rounded-xl text-gray-600 dark:text-gray-300 relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-{{ $statusColor }}-500"></div>
                                    <span class="text-xs font-bold">{{ $session->timeSlot->label }}</span>
                                    <span class="text-sm font-mono font-bold">{{ $session->timeSlot->start_time?->format('H:i') }}</span>
                                </div>

                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-lg">{{ $session->courseOffering?->subject?->name }}</h4>
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        <span class="bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 px-2 py-0.5 rounded text-xs font-medium">
                                            {{ $session->classSection?->full_name }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ $session->timeSlot->start_time?->format('H:i') }} - {{ $session->timeSlot->end_time?->format('H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-3">
                                @if($canMark)
                                    <a href="{{ route('attendance.take', $session->id) }}" wire:navigate
                                       class="inline-flex items-center px-4 py-2 {{ $isTaken ? 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100' : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm' }} text-sm font-medium rounded-lg transition-colors">
                                        @if($isTaken)
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            تعديل الغياب
                                        @else
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                            رصد الغياب
                                        @endif
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm flex items-center gap-1 cursor-not-allowed" title="لا يمكن الرصد في هذا الوقت">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $statusLabel }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">لا يوجد جدول دراسي</h3>
                <p class="text-gray-500 dark:text-gray-400">لم يتم تعيين أي حصص لك في الجدول الأسبوعي بعد.</p>
            </div>
        @endforelse
    </div>
</div>
