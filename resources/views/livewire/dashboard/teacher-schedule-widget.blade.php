<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-none">جدول حصص اليوم</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ now()->translatedFormat('l d/m/Y') }}</p>
                </div>
            </div>
            
            {{-- شارة النمط --}}
            @php
                $modeLabels = [
                    'daily_only' => 'يومي',
                    'per_period' => 'كل حصة',
                    'checkpoints' => 'نقاط تفتيش',
                ];
            @endphp
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                {{ $modeLabels[$attendanceMode] ?? $attendanceMode }}
            </span>
        </div>

        {{-- شريط الإحصائيات المصغر --}}
        @if(count($timetables) > 0)
            <div class="flex gap-4 mb-6">
                <div class="flex-1 bg-green-50 dark:bg-green-900/10 rounded-xl p-3 flex items-center justify-between border border-green-100 dark:border-green-800/30">
                    <span class="text-xs font-medium text-green-700 dark:text-green-400">تم الرصد</span>
                    <span class="text-lg font-bold text-green-700 dark:text-green-400">{{ $this->recordedCount }}</span>
                </div>
                <div class="flex-1 bg-amber-50 dark:bg-amber-900/10 rounded-xl p-3 flex items-center justify-between border border-amber-100 dark:border-amber-800/30">
                    <span class="text-xs font-medium text-amber-700 dark:text-amber-400">متبقي</span>
                    <span class="text-lg font-bold text-amber-700 dark:text-amber-400">{{ $this->pendingCount }}</span>
                </div>
            </div>
        @endif

        @if(count($timetables) > 0)
            <div class="space-y-3">
                @foreach($timetables as $timetable)
                    <div class="group relative flex items-center justify-between p-4 rounded-xl border transition-all hover:shadow-md
                        {{ $timetable->is_recorded 
                            ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' 
                            : 'bg-white dark:bg-gray-800 border-indigo-100 dark:border-indigo-900/30 ring-1 ring-indigo-50 dark:ring-indigo-900/20' }}">
                        
                        <div class="flex items-center gap-4">
                            <!-- Time Slot -->
                            @php
                                $startTime = $timetable->timeSlot?->start_time;
                                $startLabel = is_string($startTime) ? $startTime : ($startTime?->format('H:i') ?? '');
                            @endphp
                            <div class="flex flex-col items-center justify-center w-14 h-14 rounded-xl border
                                {{ $timetable->is_recorded 
                                    ? 'bg-gray-50 dark:bg-gray-700/50 border-gray-100 dark:border-gray-600 text-gray-500' 
                                    : 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-100 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400' }}">
                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $timetable->timeSlot->label }}</span>
                                <span class="text-sm font-bold">{{ $startLabel }}</span>
                            </div>

                            <!-- Class Info -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white text-base">{{ $timetable->courseOffering->subject->name }}</h4>
                                    @if($timetable->is_recorded)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            تم
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        {{ $timetable->classSection->full_name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Button -->
                        <a href="{{ route('attendance.take', $timetable->id) }}" wire:navigate 
                           class="relative z-10 px-4 py-2 text-sm font-bold rounded-lg transition-all flex items-center gap-2
                               {{ $timetable->is_recorded 
                                   ? 'text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' 
                                   : 'text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-none hover:-translate-y-0.5' }}">
                            @if(!$timetable->is_recorded)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            @endif
                            {{ $timetable->is_recorded ? 'تعديل' : 'رصد' }}
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">لا توجد حصص اليوم</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-1">استمتع بيومك! لا توجد حصص تتطلب رصد الحضور حالياً.</p>
            </div>
        @endif
    </div>
</div>
