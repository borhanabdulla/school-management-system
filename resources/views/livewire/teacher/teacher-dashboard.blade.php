<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        @if(!$teacher)
            {{-- رسالة إذا لم يكن المستخدم معلماً --}}
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-8 text-center">
                <div class="w-16 h-16 bg-amber-100 dark:bg-amber-800/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-amber-800 dark:text-amber-200 mb-2">حسابك غير مربوط بملف معلم</h3>
                <p class="text-amber-600 dark:text-amber-400">يرجى التواصل مع الإدارة لربط حسابك بملف معلم للوصول إلى لوحة التحكم.</p>
            </div>
        @else
            {{-- Hero Section --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 to-purple-700 rounded-3xl shadow-2xl">
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
                
                <div class="relative z-10 px-8 py-10 text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="px-3 py-1 rounded-full bg-white/20 text-sm font-medium backdrop-blur-sm">
                                    {{ now()->locale('ar')->translatedFormat('l, j F Y') }}
                                </span>
                                @if($activeTerm)
                                    <span class="px-3 py-1 rounded-full bg-green-400/20 text-green-100 text-sm font-medium backdrop-blur-sm border border-green-400/30">
                                        {{ $activeTerm->name }}
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-3xl font-bold font-display mb-2">
                                أهلاً، أستاذ {{ $teacher->full_name }} 👋
                            </h1>
                            <p class="text-indigo-100 text-lg">
                                @if($this->canTakeAttendance)
                                    @if($this->stats['total'] > 0)
                                        لديك اليوم <strong>{{ $this->stats['total'] }}</strong> حصة
                                        @if($this->stats['missed'] > 0)
                                            • <span class="text-red-200">{{ $this->stats['missed'] }} بانتظار الرصد</span>
                                        @endif
                                    @else
                                        لا توجد حصص مجدولة لك اليوم. استمتع بوقتك! ☕
                                    @endif
                                @else
                                    رصد الحضور غير متاح لك حسب إعدادات المدرسة.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Mini Grid --}}
            @if($this->canTakeAttendance)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total'] }}</div>
                        <div class="text-xs text-gray-500">إجمالي</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800 text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->stats['done'] }}</div>
                        <div class="text-xs text-blue-700 dark:text-blue-300">تم الرصد</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800 text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->stats['active'] }}</div>
                        <div class="text-xs text-green-700 dark:text-green-300">جارية الآن</div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 border border-red-100 dark:border-red-800 text-center">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->stats['missed'] }}</div>
                        <div class="text-xs text-red-700 dark:text-red-300">لم تُرصد</div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main: Timeline Cards --}}
                <div class="lg:col-span-2 space-y-6">
                    @if($this->canTakeAttendance)
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                                حصص اليوم
                            </h2>
                        </div>

                        @if($this->todaysTimeline->count() > 0)
                            <div class="space-y-3">
                                @foreach($this->todaysTimeline as $session)
                                @php
                                    $statusConfig = match($session['status']) {
                                        'DONE' => [
                                            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
                                            'border' => 'border-blue-200 dark:border-blue-800',
                                            'icon_bg' => 'bg-blue-100 dark:bg-blue-800/50',
                                            'icon_color' => 'text-blue-600 dark:text-blue-400',
                                            'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                            'badge_text' => 'تم الرصد ✓',
                                            'btn' => 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300',
                                            'btn_text' => 'تعديل',
                                        ],
                                        'ACTIVE' => [
                                            'bg' => 'bg-green-50 dark:bg-green-900/20',
                                            'border' => 'border-green-300 dark:border-green-700 ring-2 ring-green-200 dark:ring-green-800',
                                            'icon_bg' => 'bg-green-100 dark:bg-green-800/50 animate-pulse',
                                            'icon_color' => 'text-green-600 dark:text-green-400',
                                            'badge' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
                                            'badge_text' => 'جارية الآن 🔴',
                                            'btn' => 'bg-green-600 text-white hover:bg-green-700 shadow-lg shadow-green-200 dark:shadow-none',
                                            'btn_text' => 'رصد الحضور',
                                        ],
                                        'MISSED' => [
                                            'bg' => 'bg-red-50 dark:bg-red-900/20',
                                            'border' => 'border-red-200 dark:border-red-800',
                                            'icon_bg' => 'bg-red-100 dark:bg-red-800/50',
                                            'icon_color' => 'text-red-600 dark:text-red-400',
                                            'badge' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                                            'badge_text' => 'لم يُرصد ⚠',
                                            'btn' => 'bg-red-600 text-white hover:bg-red-700',
                                            'btn_text' => 'رصد الآن',
                                        ],
                                        default => [
                                            'bg' => 'bg-gray-50 dark:bg-gray-800',
                                            'border' => 'border-gray-200 dark:border-gray-700',
                                            'icon_bg' => 'bg-gray-100 dark:bg-gray-700',
                                            'icon_color' => 'text-gray-400',
                                            'badge' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                            'badge_text' => 'قادمة',
                                            'btn' => 'bg-gray-200 text-gray-500 cursor-not-allowed',
                                            'btn_text' => 'لاحقاً',
                                        ],
                                    };
                                @endphp

                                <div class="{{ $statusConfig['bg'] }} {{ $statusConfig['border'] }} rounded-xl p-4 border transition-all hover:shadow-md">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            {{-- Time Box --}}
                                            <div class="{{ $statusConfig['icon_bg'] }} {{ $statusConfig['icon_color'] }} w-14 h-14 rounded-xl flex flex-col items-center justify-center">
                                                <span class="text-xs font-bold">{{ $session['label'] }}</span>
                                                <span class="text-sm font-bold">{{ $session['start_time'] }}</span>
                                            </div>

                                            {{-- Info --}}
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-white">{{ $session['subject'] }}</h4>
                                                    <span class="{{ $statusConfig['badge'] }} px-2 py-0.5 rounded-full text-xs font-medium">
                                                        {{ $statusConfig['badge_text'] }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $session['section'] }} • {{ $session['start_time'] }} - {{ $session['end_time'] }}
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Action Button --}}
                                        @if($session['status'] !== 'UPCOMING')
                                            <a href="{{ route('attendance.take', $session['id']) }}" wire:navigate
                                               class="{{ $statusConfig['btn'] }} px-4 py-2 rounded-lg text-sm font-bold transition-all">
                                                {{ $statusConfig['btn_text'] }}
                                            </a>
                                        @else
                                            <span class="{{ $statusConfig['btn'] }} px-4 py-2 rounded-lg text-sm font-medium">
                                                {{ $statusConfig['btn_text'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700">
                                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">لا توجد حصص اليوم</h3>
                                <p class="text-gray-500 dark:text-gray-400">استمتع بيومك! ☕</p>
                            </div>
                        @endif
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center border border-gray-100 dark:border-gray-700">
                            <p class="text-gray-500 dark:text-gray-400">رصد الحضور غير مفعل لحسابك حسب إعدادات المدرسة.</p>
                        </div>
                    @endif

                    {{-- Weekly Schedule (Simple List) --}}
                    <div class="flex items-center justify-between mt-8">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-8 bg-purple-600 rounded-full"></span>
                            جدولي الأسبوعي
                        </h2>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-right border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                        <th class="p-4 text-sm font-bold text-gray-700 dark:text-gray-200 w-32 border-l border-gray-200 dark:border-gray-600">اليوم</th>
                                        @foreach($this->timeSlots as $slot)
                                            <th class="p-3 text-center min-w-[120px] border-l border-gray-200 dark:border-gray-600 last:border-l-0">
                                                <div class="flex flex-col items-center">
                                                    <span class="text-xs font-bold text-gray-800 dark:text-white">{{ $slot->label }}</span>
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-1">
                                                        {{ $slot->start_time ? \Carbon\Carbon::parse($slot->start_time)->format('H:i') : '' }}
                                                    </span>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($this->days as $day)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="p-4 font-bold text-gray-800 dark:text-white bg-gray-50 dark:bg-gray-800/50 border-l border-gray-200 dark:border-gray-600">
                                                {{ $day->label() }}
                                            </td>
                                            @foreach($this->timeSlots as $slot)
                                                @php
                                                    $dayValue = $day->value; 
                                                    $orderIndex = $slot->order_index;
                                                    $entry = $this->timetableMatrix[$dayValue][$orderIndex] ?? null;
                                                @endphp
                                                <td class="p-2 border-l border-gray-200 dark:border-gray-600 last:border-l-0 h-20 align-middle relative group">
                                                    @if($entry)
                                                        <div class="flex flex-col items-center justify-center h-full w-full">
                                                            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 mb-1 text-center">
                                                                {{ $entry->courseOffering->subject->name }}
                                                            </span>
                                                            <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                                                                {{ $entry->classSection->full_name }}
                                                            </span>

                                                            {{-- Hover Action --}}
                                                            @if($this->canTakeAttendance)
                                                                <a href="{{ route('attendance.take', $entry->id) }}" wire:navigate
                                                                   class="absolute inset-0 bg-indigo-600/90 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-sm">
                                                                    <span class="text-white text-xs font-bold">رصد</span>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="flex items-center justify-center h-full">
                                                            <span class="text-gray-200 dark:text-gray-700 text-lg">-</span>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- My Classes --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                            الفصول التي أدرسها
                        </h3>
                        @if($this->myClassSections->count() > 0)
                            <div class="space-y-2">
                                @foreach($this->myClassSections as $item)
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 text-sm">
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $item['section']?->full_name ?? 'غير محدد' }}</div>
                                            <div class="text-xs text-gray-500">{{ $item['subject']?->name ?? '' }}</div>
                                        </div>
                                        <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 px-2 py-1 rounded-lg">
                                            {{ $item['student_count'] }} طالب
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">لم يتم تعيين فصول لك بعد.</p>
                        @endif
                    </div>

                    {{-- Quick Actions --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4">إجراءات سريعة</h3>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- تقرير الحضور --}}
                            @if($this->canTakeAttendance)
                                <a href="{{ route('teacher.attendance.report') }}" wire:navigate class="flex flex-col items-center justify-center p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all group text-center">
                                    <svg class="w-6 h-6 text-gray-500 group-hover:text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">تقرير الحضور</span>
                                </a>
                            @endif

                            <a href="{{ route('grading.gradebooks') }}" wire:navigate class="flex flex-col items-center justify-center p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-all group text-center">
                                <svg class="w-6 h-6 text-gray-500 group-hover:text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">دفاتر الدرجات</span>
                            </a>

                            <a href="{{ route('teacher.homework.index') }}" wire:navigate class="flex flex-col items-center justify-center p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all group text-center">
                                <svg class="w-6 h-6 text-gray-500 group-hover:text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">الواجبات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
