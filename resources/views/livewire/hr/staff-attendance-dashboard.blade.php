<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <span class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </span>
                    سجل الحضور اليومي
                </h1>
                <p class="text-slate-400 mt-2">{{ $dateDisplay }}</p>
            </div>

            {{-- Date Navigation --}}
            <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl p-2">
                <button wire:click="previousDay" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <input type="date" wire:model.live="date" 
                       max="{{ now()->toDateString() }}"
                       class="px-4 py-2 bg-transparent border-0 text-white text-center focus:ring-0">
                
                <button wire:click="nextDay" 
                        @if($isToday) disabled @endif
                        class="p-2 hover:bg-white/10 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                @if(!$isToday)
                    <button wire:click="goToToday" class="px-3 py-2 bg-emerald-500/20 text-emerald-400 rounded-lg text-sm hover:bg-emerald-500/30 transition-colors">
                        اليوم
                    </button>
                @endif
            </div>
        </div>

        {{-- Holiday Banner --}}
        @if($isHoliday)
            <div class="mb-8 p-6 bg-gradient-to-r from-amber-500/20 to-orange-500/20 border border-amber-500/30 rounded-2xl">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-amber-400">{{ $holidayName }}</h2>
                        <p class="text-amber-300/70">هذا اليوم عطلة رسمية - لا يتم احتساب الغياب</p>
                    </div>
                </div>
                
                @if(count($attendanceData) > 0)
                    <p class="mt-4 text-amber-300/80 text-sm">
                        <span class="font-bold">{{ count($attendanceData) }}</span> موظف يعملون في العطل الرسمية
                    </p>
                @endif
            </div>
        @endif

        {{-- Stats Cards --}}
        @if(!$isHoliday || count($attendanceData) > 0)
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-white">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-slate-400 text-sm mt-1">الإجمالي</p>
                </div>
                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-emerald-400">{{ $stats['present'] ?? 0 }}</p>
                    <p class="text-emerald-400/70 text-sm mt-1">حاضر</p>
                </div>
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-amber-400">{{ $stats['late'] ?? 0 }}</p>
                    <p class="text-amber-400/70 text-sm mt-1">متأخر</p>
                </div>
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-red-400">{{ $stats['absent'] ?? 0 }}</p>
                    <p class="text-red-400/70 text-sm mt-1">غائب</p>
                </div>
                <div class="bg-violet-500/10 border border-violet-500/20 rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-violet-400">{{ $stats['attendance_rate'] ?? 0 }}%</p>
                    <p class="text-violet-400/70 text-sm mt-1">نسبة الحضور</p>
                </div>
            </div>
        @endif

        {{-- Filters --}}
        @if(!$isHoliday || count($attendanceData) > 0)
            <div class="flex flex-wrap gap-4 mb-6" x-data="{ search: '' }">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-model="search" 
                               class="w-full pr-10 pl-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                               placeholder="بحث باسم الموظف أو الرقم الوظيفي...">
                    </div>
                </div>

                <select wire:model.live="shiftId" 
                        class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    <option value="" class="bg-slate-800">جميع الورديات</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" class="bg-slate-800">{{ $shift->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="statusFilter" 
                        class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    <option value="" class="bg-slate-800">جميع الحالات</option>
                    <option value="present" class="bg-slate-800">حاضر</option>
                    <option value="late" class="bg-slate-800">متأخر</option>
                    <option value="absent" class="bg-slate-800">غائب</option>
                    <option value="pending" class="bg-slate-800">لم يسجّل</option>
                </select>
            </div>
        @endif

        {{-- Alerts Section --}}
        @if(!empty($alerts))
            <div class="mb-6 space-y-3">
                @foreach($alerts as $alert)
                    <div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Info</span>
                        <div>
                            <span class="font-medium">تنبيه:</span> {{ $alert }}
                        </div>
                        <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-1" aria-label="Close" wire:click="dismissAlert('{{ $loop->index }}')">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Attendance Table --}}
        @if(!$isHoliday || count($attendanceData) > 0)
            <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-white/5">
                        <tr>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-300">الموظف</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">الوردية</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">الدخول</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">الخروج</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">الحالة</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">التأخير</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($filteredData as $item)
                            <tr class="hover:bg-white/5 transition-colors" 
                                wire:key="staff-{{ $item['staff_id'] }}"
                                x-show="search === '' || '{{ $item['staff']['first_name'] }} {{ $item['staff']['last_name'] }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $item['staff']['employee_number'] ?? '' }}'.toLowerCase().includes(search.toLowerCase())"
                            >
                                {{-- Employee --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-slate-600 to-slate-700 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ mb_substr($item['staff']['first_name'] ?? '', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ $item['staff']['first_name'] ?? '' }} {{ $item['staff']['last_name'] ?? '' }}</p>
                                            @if($item['staff']['teacher'] ?? false)
                                                <span class="text-xs text-violet-400">معلم</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Shift --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="text-slate-300 text-sm">{{ $item['shift']['name'] ?? '-' }}</span>
                                    @if($item['shift'])
                                        <p class="text-xs text-slate-500 font-mono">{{ $item['shift']['schedule_display'] ?? '' }}</p>
                                    @endif
                                </td>

                                {{-- Check In --}}
                                <td class="px-6 py-4 text-center">
                                    <input type="time" 
                                           wire:model.blur="attendanceData.{{ $loop->index }}.check_in"
                                           class="bg-transparent border-0 border-b border-slate-600 text-white text-center focus:ring-0 focus:border-emerald-500 w-24 p-0 pb-1">
                                </td>

                                {{-- Check Out --}}
                                <td class="px-6 py-4 text-center">
                                    <input type="time" 
                                           wire:model.blur="attendanceData.{{ $loop->index }}.check_out"
                                           class="bg-transparent border-0 border-b border-slate-600 text-white text-center focus:ring-0 focus:border-emerald-500 w-24 p-0 pb-1">
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusColors = [
                                            'present' => 'bg-emerald-500/20 text-emerald-400',
                                            'late' => 'bg-amber-500/20 text-amber-400',
                                            'absent' => 'bg-red-500/20 text-red-400',
                                            'excused' => 'bg-blue-500/20 text-blue-400',
                                            'pending' => 'bg-slate-500/20 text-slate-400',
                                        ];
                                        $statusNames = [
                                            'present' => 'حاضر',
                                            'late' => 'متأخر',
                                            'absent' => 'غائب',
                                            'excused' => 'معذور',
                                            'pending' => 'لم يسجّل',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-sm {{ $statusColors[$item['status']] ?? $statusColors['pending'] }}">
                                        {{ $statusNames[$item['status']] ?? $item['status'] }}
                                    </span>
                                </td>

                                {{-- Delay --}}
                                <td class="px-6 py-4 text-center">
                                    @if($item['delay_minutes'] > 0)
                                        <span class="text-red-400 font-bold">{{ $item['delay_minutes'] }} د</span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if(!$item['is_saved'])
                                            <button wire:click="markPresent({{ $item['staff_id'] }})" 
                                                    class="px-3 py-1.5 bg-emerald-500/20 text-emerald-400 rounded-lg text-sm hover:bg-emerald-500/30 transition-colors">
                                                حاضر
                                            </button>
                                            <button wire:click="markAbsent({{ $item['staff_id'] }})" 
                                                    class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded-lg text-sm hover:bg-red-500/30 transition-colors">
                                                غائب
                                            </button>
                                        @else
                                            <button wire:click="openEditModal({{ $item['staff_id'] }})" 
                                                    class="p-1.5 bg-white/5 text-slate-400 rounded-lg hover:bg-white/10 hover:text-white transition-colors"
                                                    title="تعديل الملاحظات">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-400">لا يوجد موظفون لعرضهم</p>
                                        <p class="text-slate-500 text-sm mt-1">تأكد من تعيين ورديات للموظفين</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeEditModal"></div>

                <div class="relative bg-slate-800 border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl">
                    <h2 class="text-xl font-bold text-white mb-6">تعديل الحضور</h2>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">وقت الدخول</label>
                            <input type="time" wire:model="editCheckIn" 
                                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">وقت الخروج</label>
                            <input type="time" wire:model="editCheckOut" 
                                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">ملاحظات</label>
                            <textarea wire:model="editRemarks" rows="3"
                                      class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                      placeholder="سبب التعديل أو ملاحظات إضافية..."></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                            <button wire:click="closeEditModal" 
                                    class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white rounded-xl transition-colors">
                                إلغاء
                            </button>
                            <button wire:click="saveEditedAttendance" 
                                    class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold hover:from-emerald-700 hover:to-teal-700 transition-all">
                                حفظ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Substitution Modal --}}
    @if($showSubstitutionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeSubstitutionModal"></div>

                <div class="relative bg-slate-800 border border-white/10 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
                    <div class="w-16 h-16 bg-amber-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>

                    <h2 class="text-xl font-bold text-white text-center mb-2">تنبيه غياب معلم</h2>
                    <p class="text-slate-300 text-center mb-6">
                        المعلم <span class="text-white font-semibold">{{ $absentTeacher?->full_name }}</span> لديه حصص في الجدول الدراسي لهذا اليوم.
                        <br>
                        هل ترغب في إدارة الاحتياط وتكليف معلم بديل؟
                    </p>

                    <div class="flex justify-center gap-3">
                        <button wire:click="closeSubstitutionModal" 
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white rounded-xl transition-colors">
                            تجاهل
                        </button>
                        <button wire:click="goToSubstitution" 
                                class="px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-xl font-semibold hover:from-amber-700 hover:to-orange-700 transition-all">
                            إدارة الاحتياط
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</div>
