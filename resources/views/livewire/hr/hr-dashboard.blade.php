<div>
<div class="space-y-8">
    {{-- Header Section --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-8 text-white shadow-xl">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-64 w-64 rounded-full bg-purple-500/20 blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <h2 class="text-4xl font-bold font-display tracking-tight">لوحة الموارد البشرية</h2>
                <p class="mt-2 text-indigo-100 text-lg opacity-90">نظرة شاملة على أداء الفريق، الإجازات، والحضور.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('hr.leave.approvals') }}" class="group flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl">
                    <span class="text-indigo-100 group-hover:text-white">إدارة الطلبات</span>
                    <svg class="w-5 h-5 text-indigo-200 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </a>
                <a href="{{ route('hr.attendance.index') }}" class="group flex items-center gap-2 px-5 py-2.5 bg-white text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl font-medium">
                    <span>سجل الحضور</span>
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Staff -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:rotate-6 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        +2%
                    </span>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['total_staff'] }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">إجمالي الموظفين</p>
                
                {{-- Sparkline --}}
                <div class="absolute bottom-0 left-0 right-0 h-12 opacity-10">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 30 Q 20 20, 40 30 T 100 10" fill="none" stroke="currentColor" stroke-width="4" class="text-blue-600" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- On Leave Today -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 dark:bg-orange-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400 group-hover:rotate-6 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    @if($stats['total_staff'] > 0)
                        <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $stats['on_leave_percentage'] > 10 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' }}">
                            {{ $stats['on_leave_percentage'] }}%
                        </span>
                    @endif
                </div>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['on_leave_today'] }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">في إجازة اليوم</p>
                
                {{-- Sparkline --}}
                <div class="absolute bottom-0 left-0 right-0 h-12 opacity-10">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 20 Q 30 35, 60 10 T 100 30" fill="none" stroke="currentColor" stroke-width="4" class="text-orange-600" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 dark:bg-purple-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 group-hover:rotate-6 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    @if(isset($stats['urgent_requests']) && $stats['urgent_requests'] > 0)
                        <span class="flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 animate-pulse">
                            <span>{{ $stats['urgent_requests'] }}</span>
                            <span>عاجل</span>
                        </span>
                    @endif
                </div>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['pending_requests'] }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">طلبات معلقة</p>
                
                {{-- Sparkline --}}
                <div class="absolute bottom-0 left-0 right-0 h-12 opacity-10">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 35 Q 25 10, 50 25 T 100 15" fill="none" stroke="currentColor" stroke-width="4" class="text-purple-600" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Alerts -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-pink-50 dark:bg-pink-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl {{ $stats['active_alerts'] > 0 ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }} flex items-center justify-center group-hover:rotate-6 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['active_alerts'] }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">تنبيهات النظام</p>
                
                {{-- Sparkline --}}
                <div class="absolute bottom-0 left-0 right-0 h-12 opacity-10">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 40">
                        <path d="M0 25 Q 40 40, 70 10 T 100 20" fill="none" stroke="currentColor" stroke-width="4" class="text-pink-600" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Middle Section: Heatmap & Alerts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Week Heatmap (Bar Chart) --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-1 h-6 bg-indigo-500 rounded-full"></span>
                        نظرة الأسبوع القادم
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">توقعات الغياب للأيام السبعة القادمة</p>
                </div>
                <div class="flex gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> منخفض</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span> متوسط</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> مرتفع</span>
                </div>
            </div>
            
            <div class="h-48 flex items-end justify-between gap-2 px-2">
                @foreach($weekHeatmap as $day)
                    <div wire:click="selectDate('{{ $day['date'] }}')" 
                         class="group relative flex-1 flex flex-col items-center cursor-pointer transition-all duration-300 hover:scale-105">
                        
                        {{-- Tooltip --}}
                        <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10 pointer-events-none">
                            <div class="bg-gray-900 text-white text-xs rounded-lg py-1.5 px-3 shadow-lg whitespace-nowrap">
                                {{ $day['count'] }} غياب متوقع
                            </div>
                            <div class="w-2 h-2 bg-gray-900 rotate-45 mx-auto -mt-1"></div>
                        </div>

                        {{-- Bar --}}
                        <div style="height: {{ $day['height_percentage'] }}%" 
                             class="w-full max-w-[40px] rounded-t-lg {{ $day['color_class'] }} opacity-80 group-hover:opacity-100 transition-all relative overflow-hidden
                             {{ $selectedDate === $day['date'] ? 'ring-2 ring-indigo-500 ring-offset-2 dark:ring-offset-gray-800 opacity-100' : '' }}">
                             <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                        </div>
                        
                        {{-- Label --}}
                        <div class="mt-3 text-center">
                            <div class="text-xs font-bold text-gray-700 dark:text-gray-300 {{ $day['is_today'] ? 'text-indigo-600 dark:text-indigo-400' : '' }}">
                                {{ $day['day_name'] }}
                            </div>
                            <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Smart Alerts (Compact) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-1 h-6 bg-red-500 rounded-full"></span>
                    تنبيهات
                </h3>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">{{ count($alerts) }}</span>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar space-y-3 pr-1 -mr-1 max-h-[250px]">
                @forelse($alerts as $alert)
                    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-700/50 bg-gray-50 dark:bg-gray-700/20 hover:bg-white dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 mt-0.5">
                                @if($alert['severity'] === 'critical')
                                    <div class="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.6)]"></div>
                                @elseif($alert['severity'] === 'danger')
                                    <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                                @else
                                    <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate" title="{{ $alert['title'] }}">{{ $alert['title'] }}</p>
                                @if(isset($alert['staff_names']) && $alert['staff_names'])
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $alert['staff_names'] }}</p>
                                @endif
                                <a href="{{ $alert['action_url'] }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium hover:underline mt-1 inline-block">
                                    {{ $alert['action_label'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <p class="text-sm">لا توجد تنبيهات نشطة</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom Section: Affected Classes & Calendar --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        {{-- Affected Classes (Grid View) --}}
        <div class="xl:col-span-2 space-y-6">
            @if($affectedClasses && $affectedClasses['total_affected'] > 0)
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-2xl">📚</span>
                            الحصص المتأثرة - {{ $affectedClasses['date_formatted'] }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                            <span class="font-bold text-red-600">{{ $affectedClasses['total_affected'] }}</span> حصة تحتاج إلى تغطية من <span class="font-bold text-gray-800 dark:text-gray-200">{{ $affectedClasses['teachers_count'] }}</span> معلم غائب
                        </p>
                    </div>
                    <button wire:click="loadAffectedClasses" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700" title="تحديث">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($affectedClasses['by_teacher'] as $teacherGroup)
                        <div x-data="{ expanded: false }" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-300">
                            {{-- Card Header --}}
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/20">
                                                {{ mb_substr($teacherGroup['teacher_name'], 0, 1) }}
                                            </div>
                                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-red-500 border-4 border-white dark:border-gray-800 rounded-full"></div>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white text-lg leading-tight">{{ $teacherGroup['teacher_name'] }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $teacherGroup['classes_count'] }} حصص متأثرة</p>
                                        </div>
                                    </div>
                                    <button @click="expanded = !expanded" class="text-gray-400 hover:text-indigo-600 transition-colors">
                                        <svg class="w-6 h-6 transform transition-transform duration-300" :class="{'rotate-180': expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                </div>

                                {{-- Quick Stats --}}
                                <div class="flex items-center gap-2 mb-4">
                                    @foreach(collect($teacherGroup['classes'])->take(4) as $class)
                                        <div class="px-2 py-1 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $class['has_substitute'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                            {{ $class['subject_name'] }}
                                        </div>
                                    @endforeach
                                    @if(count($teacherGroup['classes']) > 4)
                                        <span class="text-xs text-gray-400">+{{ count($teacherGroup['classes']) - 4 }}</span>
                                    @endif
                                </div>

                                {{-- Primary Action --}}
                                @php
                                    $firstUnassigned = collect($teacherGroup['classes'])->firstWhere('has_substitute', false);
                                @endphp
                                @if($firstUnassigned)
                                    <button 
                                        wire:click="openSubstituteModal({{ $firstUnassigned['timetable_id'] }}, {{ $teacherGroup['teacher_id'] }}, {{ $firstUnassigned['leave_id'] ?? 'null' }})"
                                        
                                        class="w-full py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-300 font-bold text-sm hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors flex items-center justify-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        تعيين بديل للحصة {{ $firstUnassigned['order_index'] }}
                                    </button>
                                @else
                                    <div class="w-full py-2.5 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-300 font-bold text-sm flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        تم تغطية جميع الحصص
                                    </div>
                                @endif
                            </div>

                            {{-- Expanded Details --}}
                            <div x-show="expanded" x-collapse class="border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($teacherGroup['classes'] as $class)
                                        <div class="px-5 py-3 flex items-center justify-between hover:bg-white dark:hover:bg-gray-700/30 transition-colors">
                                            <div class="flex items-center gap-4">
                                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center font-mono font-bold text-gray-500 dark:text-gray-400 text-sm">
                                                    {{ $class['order_index'] }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $class['subject_name'] }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $class['class_section_name'] }} • {{ $class['time_slot'] }}</p>
                                                </div>
                                            </div>
                                            
                                            @if($class['has_substitute'])
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-green-600 dark:text-green-400">{{ $class['substitute_name'] }}</span>
                                                    <button wire:click="openSubstituteModal({{ $class['timetable_id'] }}, {{ $teacherGroup['teacher_id'] }}, {{ $class['leave_id'] ?? 'null' }})" class="text-gray-400 hover:text-indigo-600">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    </button>
                                                </div>
                                            @else
                                                <button wire:click="openSubstituteModal({{ $class['timetable_id'] }}, {{ $teacherGroup['teacher_id'] }}, {{ $class['leave_id'] ?? 'null' }})" class="text-xs px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                                                    تعيين
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($affectedClasses && $affectedClasses['total_affected'] === 0)
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-100 dark:border-green-900/50 p-12 text-center">
                    <div class="w-24 h-24 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce shadow-lg shadow-green-500/20">
                        <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-green-800 dark:text-green-300 mb-2">يوم مثالي!</h3>
                    <p class="text-green-600 dark:text-green-400 text-lg">لا توجد حصص متأثرة ليوم {{ $affectedClasses['date_formatted'] }}</p>
                    <p class="text-green-500/80 mt-2">جميع المعلمين متواجدون أو تم تغطية حصصهم</p>
                </div>
            @endif
        </div>

        {{-- Calendar & Pending Requests (Sidebar) --}}
        <div class="space-y-8">
            {{-- Calendar Widget --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-1 h-6 bg-purple-500 rounded-full"></span>
                        التقويم
                    </h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="previousMonth" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $calendarDate->translatedFormat('F Y') }}
                        </span>
                        <button wire:click="nextMonth" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    @foreach(['أحد', 'إثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'] as $day)
                        <div class="bg-gray-50 dark:bg-gray-800 py-2 text-center text-[10px] font-bold text-gray-500 dark:text-gray-400">
                            {{ $day }}
                        </div>
                    @endforeach

                    @foreach($calendarDays as $day)
                        <div class="h-16 bg-white dark:bg-gray-800 p-1 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50 flex flex-col items-center
                            {{ !$day['isCurrentMonth'] ? 'bg-gray-50/50 dark:bg-gray-900/50' : '' }}
                        ">
                            <span class="text-xs w-6 h-6 flex items-center justify-center rounded-full mb-1
                                {{ $day['isToday'] ? 'bg-indigo-600 text-white font-bold' : 
                                   ($day['isCurrentMonth'] ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-600') }}">
                                {{ $day['date']->day }}
                            </span>
                            
                            <div class="flex flex-wrap justify-center gap-0.5 w-full">
                                @foreach($day['leaves']->take(4) as $leave)
                                    <div class="w-1.5 h-1.5 rounded-full {{ $leave->status === 'approved' ? 'bg-blue-500' : 'bg-gray-400' }}" title="{{ $leave->staff->full_name }}"></div>
                                @endforeach
                                @if($day['leaves']->count() > 4)
                                    <span class="text-[8px] text-gray-400">+</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pending Requests Widget --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-1 h-6 bg-orange-500 rounded-full"></span>
                        طلبات معلقة
                    </h3>
                    <a href="{{ route('hr.leave.approvals') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:underline">
                        عرض الكل
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($pendingRequests->take(5) as $request)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/20 border border-gray-100 dark:border-gray-700/50 hover:bg-white hover:shadow-sm transition-all">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ mb_substr($request->staff->first_name, 0, 1) }}
                                </div>
                                @if($request->is_urgent)
                                    <div class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $request->staff->full_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $request->leaveType->name }} • {{ $request->days_count }} يوم</p>
                            </div>
                            <a href="{{ route('hr.leave.approvals') }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-sm">لا توجد طلبات معلقة</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Substitute Selection Modal --}}
    @if($showSubstituteModal)
    <div 
        x-data
        x-init="$nextTick(() => { $el.classList.add('opacity-100'); $el.querySelector('.modal-content').classList.add('scale-100', 'opacity-100'); })"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300"
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
             @click="$wire.closeSubstituteModal(); $el.closest('[x-data]').remove()"></div>

        {{-- Modal Panel --}}
        <div class="modal-content bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300 relative z-10 flex flex-col max-h-[90vh]">
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-white dark:bg-gray-800 sticky top-0 z-20">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-2xl">🔄</span>
                        اختيار معلم بديل
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">اختر المعلم الأنسب لتغطية الحصة</p>
                </div>
                <button @click="$wire.closeSubstituteModal(); $el.closest('[x-data]').remove()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                @if(count($suggestedSubstitutes) > 0)
                    {{-- Best Match Highlight (First Item) --}}
                    @php $bestMatch = $suggestedSubstitutes[0]; @endphp
                    @if($bestMatch['match_percentage'] >= 90)
                        <div class="mb-6 p-1 rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-3 text-indigo-600 dark:text-indigo-400 font-bold text-sm uppercase tracking-wider">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    أفضل تطابق مقترح
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-2xl font-bold text-gray-600 dark:text-gray-300">
                                            {{ mb_substr($bestMatch['teacher_name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $bestMatch['teacher_name'] }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $bestMatch['specialization'] }}</p>
                                            <div class="flex items-center gap-3 mt-2">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    {{ $bestMatch['match_percentage'] }}% تطابق
                                                </span>
                                                <span class="text-xs text-gray-400 border-r border-gray-200 dark:border-gray-700 pr-3 mr-1">
                                                    {{ $bestMatch['daily_load'] }} حصص اليوم
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <button 
                                        wire:click="assignSubstitute({{ $bestMatch['teacher_id'] }})"
                                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-105 active:scale-95"
                                    >
                                        تعيين الآن
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 px-1">معلمون آخرون متاحون</p>
                        @foreach($suggestedSubstitutes as $index => $substitute)
                            @if($index === 0 && $substitute['match_percentage'] >= 90) @continue @endif
                            
                            <div class="group flex items-center justify-between p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-all duration-200">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-300 font-bold text-lg group-hover:bg-white dark:group-hover:bg-gray-600 transition-colors">
                                            {{ mb_substr($substitute['teacher_name'], 0, 1) }}
                                        </div>
                                        {{-- Match Ring --}}
                                        <svg class="absolute -top-1 -left-1 w-14 h-14 transform -rotate-90 pointer-events-none" viewBox="0 0 36 36">
                                            <path class="text-gray-100 dark:text-gray-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="2" />
                                            <path class="{{ $substitute['match_percentage'] >= 80 ? 'text-green-500' : ($substitute['match_percentage'] >= 50 ? 'text-yellow-500' : 'text-orange-500') }}" 
                                                  stroke-dasharray="{{ $substitute['match_percentage'] }}, 100" 
                                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                                  fill="none" stroke="currentColor" stroke-width="2" />
                                        </svg>
                                    </div>
                                    
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ $substitute['teacher_name'] }}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $substitute['specialization'] }}</span>
                                            <span class="text-gray-300">•</span>
                                            <span class="text-xs font-medium {{ $substitute['daily_load'] > 3 ? 'text-orange-500' : 'text-green-500' }}">
                                                {{ $substitute['daily_load'] }} حصص
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-1">{{ $substitute['match_reason'] }}</p>
                                    </div>
                                </div>

                                <button 
                                    wire:click="assignSubstitute({{ $substitute['teacher_id'] }})"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all"
                                >
                                    تعيين
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mb-4">
                            <span class="text-4xl">😕</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">لا يوجد معلمون متاحون</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                            جميع المعلمين لديهم حصص في هذا الوقت أو تجاوزوا الحد الأقصى للنصاب اليومي.
                        </p>
                        <button wire:click="closeSubstituteModal" class="mt-6 text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                            إغلاق والعودة
                        </button>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                <button wire:click="closeSubstituteModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

