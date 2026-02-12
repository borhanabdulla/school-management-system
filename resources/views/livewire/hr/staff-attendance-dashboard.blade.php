<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-7xl mx-auto space-y-6">
        
        {{-- Header --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </span>
                    سجل الحضور اليومي
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 mr-14">{{ $dateDisplay }}</p>
            </div>

            {{-- Date Navigation --}}
            <div class="flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-1.5 shadow-sm">
                <button wire:click="nextDay" 
                        @if($isToday) disabled @endif
                        class="p-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <div class="relative">
                    <input type="date" wire:model.live="date" 
                           max="{{ now()->toDateString() }}"
                           class="px-2 py-1 bg-transparent border-0 text-gray-900 dark:text-white font-medium text-center focus:ring-0 cursor-pointer">
                </div>
                
                <button wire:click="previousDay" class="p-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                @if(!$isToday)
                    <div class="border-r border-gray-200 dark:border-gray-700 h-6 mx-1"></div>
                    <button wire:click="goToToday" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                        اليوم
                    </button>
                @endif
            </div>
        </div>

        {{-- Holiday Banner --}}
        @if($isHoliday)
            <div class="p-6 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-2xl shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-amber-800 dark:text-amber-400">{{ $holidayName }}</h2>
                        <p class="text-amber-600 dark:text-amber-500/80">هذا اليوم عطلة رسمية - لا يتم احتساب الغياب تلقائياً</p>
                    </div>
                </div>
                
                @if(count($attendanceData) > 0)
                    <p class="mt-4 mr-16 text-amber-700 dark:text-amber-400 text-sm">
                        <span class="font-bold">{{ count($attendanceData) }}</span> موظف يعملون في العطل الرسمية
                    </p>
                @endif
            </div>
        @endif

        {{-- Stats Cards --}}
        @if(!$isHoliday || count($attendanceData) > 0)
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                {{-- Total --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">إجمالي الموظفين</p>
                </div>
                {{-- Present --}}
                <div class="bg-white dark:bg-gray-800 border-b-4 border-emerald-500 rounded-xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['present'] ?? 0 }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">حاضر</p>
                </div>
                {{-- Late --}}
                <div class="bg-white dark:bg-gray-800 border-b-4 border-amber-500 rounded-xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['late'] ?? 0 }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">متأخر</p>
                </div>
                {{-- Absent --}}
                <div class="bg-white dark:bg-gray-800 border-b-4 border-red-500 rounded-xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['absent'] ?? 0 }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">غائب</p>
                </div>
                {{-- Rate --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center shadow-sm relative overflow-hidden">
                    <div class="absolute inset-0 bg-indigo-50 dark:bg-indigo-900/10 opacity-50"></div>
                    <div class="relative z-10">
                        <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['attendance_rate'] ?? 0 }}%</p>
                        <p class="text-indigo-600/70 dark:text-indigo-300/70 text-sm mt-1">نسبة الحضور</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filters & Table --}}
        @if(!$isHoliday || count($attendanceData) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden" x-data="{ search: '' }">
                
                {{-- Toolbar --}}
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[240px]">
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" x-model="search" 
                                   class="w-full pr-10 pl-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="بحث باسم الموظف أو الرقم الوظيفي...">
                        </div>
                    </div>

                    <select wire:model.live="shiftId" 
                            class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="">جميع الورديات</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="statusFilter" 
                            class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="">جميع الحالات</option>
                        <option value="present">حاضر</option>
                        <option value="late">متأخر</option>
                        <option value="absent">غائب</option>
                        <option value="pending">لم يسجّل</option>
                    </select>
                </div>

                {{-- Alerts Section --}}
                @if(!empty($alerts))
                    <div class="p-4 space-y-3 bg-red-50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/20">
                        @foreach($alerts as $alert)
                            <div class="flex items-center p-3 text-sm text-red-800 dark:text-red-400 rounded-lg bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800/50 shadow-sm" role="alert">
                                <svg class="flex-shrink-0 inline w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                </svg>
                                <span class="font-bold ml-1">تنبيه:</span> {{ $alert }}
                                <button type="button" class="mr-auto -my-1.5 p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition" wire:click="dismissAlert('{{ $loop->index }}')">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase font-medium">
                            <tr>
                                <th class="px-6 py-4">الموظف</th>
                                <th class="px-6 py-4 text-center">الوردية</th>
                                <th class="px-6 py-4 text-center">الدخول</th>
                                <th class="px-6 py-4 text-center">الخروج</th>
                                <th class="px-6 py-4 text-center">الحالة</th>
                                <th class="px-6 py-4 text-center">التأخير</th>
                                <th class="px-6 py-4">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($filteredData as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors bg-white dark:bg-gray-800" 
                                    wire:key="staff-{{ $item['staff_id'] }}"
                                    x-show="search === '' || '{{ $item['staff']['first_name'] }} {{ $item['staff']['last_name'] }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $item['staff']['employee_number'] ?? '' }}'.toLowerCase().includes(search.toLowerCase())">
                                    
                                    {{-- Employee --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold border border-gray-200 dark:border-gray-600">
                                                {{ mb_substr($item['staff']['first_name'] ?? '', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-gray-900 dark:text-white font-bold">{{ $item['staff']['first_name'] ?? '' }} {{ $item['staff']['last_name'] ?? '' }}</p>
                                                @if($item['staff']['teacher'] ?? false)
                                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">معلم</span>
                                                @else
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item['staff']['title'] ?? 'موظف' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Shift --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="block text-gray-900 dark:text-white font-medium">{{ $item['shift']['name'] ?? '-' }}</span>
                                        @if($item['shift'])
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5" dir="ltr">{{ $item['shift']['schedule_display'] ?? '' }}</p>
                                        @endif
                                    </td>

                                    {{-- Check In --}}
                                    <td class="px-6 py-4 text-center">
                                        <input type="time" 
                                               wire:model.blur="attendanceData.{{ $loop->index }}.check_in"
                                               class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white text-center rounded-lg focus:ring-2 focus:ring-indigo-500 w-28 text-sm px-1 py-1">
                                    </td>

                                    {{-- Check Out --}}
                                    <td class="px-6 py-4 text-center">
                                        <input type="time" 
                                               wire:model.blur="attendanceData.{{ $loop->index }}.check_out"
                                               class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white text-center rounded-lg focus:ring-2 focus:ring-indigo-500 w-28 text-sm px-1 py-1">
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $status = $item['status'] ?? 'pending';
                                            $colors = [
                                                'present' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                'late' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                'absent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'pending' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                            ];
                                            $labels = [
                                                'present' => 'حاضر', 
                                                'late' => 'متأخر', 
                                                'absent' => 'غائب', 
                                                'pending' => 'غير مسجل'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $colors[$status] }}">
                                            {{ $labels[$status] }}
                                        </span>
                                    </td>

                                    {{-- Late Minutes --}}
                                    <td class="px-6 py-4 text-center">
                                        @if(($item['delay_minutes'] ?? 0) > 0)
                                            <span class="text-red-600 dark:text-red-400 font-bold text-sm">{{ $item['delay_minutes'] }} دقيقة</span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">-</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <button wire:click="saveAttendance('{{ $loop->index }}')" class="p-1.5 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition" title="حفظ">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                            @if(($item['status'] ?? 'pending') !== 'pending')
                                                <button wire:click="resetAttendance('{{ $loop->index }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition" title="حذف السجل">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        لا توجد بيانات للعرض
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Global Actions --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button wire:click="saveAll" wire:loading.attr="disabled" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <svg wire:loading.class="hidden" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <svg wire:loading class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        حفظ جميع التغييرات
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
