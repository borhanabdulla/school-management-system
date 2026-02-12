<div class="min-h-screen bg-gray-50/50 dark:bg-gray-900/50 pb-12" x-data="{ activeTab: 'overview' }">
    {{-- Hero Section --}}
    <div class="relative bg-white dark:bg-gray-800 pb-12 shadow-sm border-b border-gray-200 dark:border-gray-700">
        {{-- Cover Image / Gradient --}}
        <div class="h-48 w-full bg-gradient-to-r from-indigo-600 to-purple-600 overflow-hidden">
            <div class="absolute inset-0 bg-pattern opacity-10"></div> {{-- Optional pattern overlay --}}
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative -mt-16 sm:flex sm:items-end sm:space-x-5 rtl:space-x-reverse">
                {{-- Avatar --}}
                <div class="relative group">
                    <div class="h-32 w-32 rounded-2xl ring-4 ring-white dark:ring-gray-800 bg-white dark:bg-gray-700 flex items-center justify-center text-4xl font-bold text-indigo-600 dark:text-indigo-400 shadow-lg overflow-hidden">
                        @if($staff->user && $staff->user->profile_photo_url)
                            <img src="{{ $staff->user->profile_photo_url }}" alt="{{ $staff->full_name }}" class="h-full w-full object-cover">
                        @else
                            {{ mb_substr($staff->first_name, 0, 1) }}
                        @endif
                    </div>
                    <div class="absolute bottom-2 right-2 h-5 w-5 rounded-full border-2 border-white dark:border-gray-800 {{ $staff->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                </div>

                {{-- Main Info --}}
                <div class="mt-6 sm:flex-1 sm:min-w-0 sm:flex sm:items-center sm:justify-between sm:space-x-6 sm:pb-1 rtl:space-x-reverse">
                    <div class="sm:hidden md:block min-w-0 flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white truncate">
                            {{ $staff->full_name }}
                        </h1>
                        <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6 rtl:space-x-reverse">
                            <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $staff->job_title }}
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                {{ $staff->employee_number }}
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                انضم في {{ $staff->joining_date?->format('Y-m-d') }}
                            </div>
                        </div>
                    </div>
                    
                    {{-- Actions --}}
                    <div class="mt-6 flex flex-col justify-stretch space-y-3 sm:flex-row sm:space-y-0 sm:space-x-4 rtl:space-x-reverse">
                        <a href="{{ route('hr.staff.edit', $staff) }}" class="inline-flex justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <span>تعديل</span>
                        </a>
                        <button type="button" class="inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>إجراء إداري</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <div class="mt-8 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8 rtl:space-x-reverse overflow-x-auto" aria-label="Tabs">
                    @foreach([
                        'overview' => ['label' => 'نظرة عامة', 'icon' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2'],
                        'attendance' => ['label' => 'سجل الحضور', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'leaves' => ['label' => 'الإجازات', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        'financial' => ['label' => 'البيانات المالية', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'access' => ['label' => 'الصلاحيات', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                    ] as $key => $tab)
                        <button type="button" 
                            x-on:click.prevent="activeTab = '{{ $key }}'"
                            wire:key="tab-{{ $key }}"
                            :class="activeTab === '{{ $key }}' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300'"
                            class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">
                            <svg :class="activeTab === '{{ $key }}' ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500'"
                                class="-ml-0.5 mr-2 h-5 w-5 rtl:ml-2 rtl:mr-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                            </svg>
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>

    {{-- Content Area --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        
        {{-- Overview Tab --}}
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Personal Info Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            المعلومات الشخصية
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">البريد الإلكتروني</label>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-gray-900 dark:text-white font-medium">{{ $staff->email }}</span>
                                @if($staff->user)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">مفعل</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">رقم الهاتف</label>
                            <p class="mt-1 text-gray-900 dark:text-white font-medium" dir="ltr">{{ $staff->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">تاريخ الالتحاق</label>
                            <p class="mt-1 text-gray-900 dark:text-white font-medium">{{ $staff->joining_date?->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">الحالة</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $staff->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $staff->status === 'active' ? 'نشط' : $staff->status }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Job Info Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            بيانات الوظيفة
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">المسمى الوظيفي</label>
                            <p class="mt-1 text-gray-900 dark:text-white font-medium">{{ $staff->job_title }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">نوع التوظيف</label>
                            <p class="mt-1 text-gray-900 dark:text-white font-medium">{{ $staff->employment_type_name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">فترة الدوام</label>
                            <div class="mt-1">
                                @if($staff->workShift)
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $staff->workShift->name }}</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($staff->workShift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($staff->workShift->end_time)->format('H:i') }}</p>
                                @else
                                    <p class="text-gray-500 italic">غير محدد</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Teacher Info (Conditional) --}}
                @if($staff->teacher)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-purple-50/50 dark:bg-purple-900/20">
                            <h3 class="text-lg font-bold text-purple-900 dark:text-purple-100 flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                                الملف الأكاديمي
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                            <div>
                                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">التخصص</label>
                                <p class="mt-1 text-gray-900 dark:text-white font-medium">{{ $staff->teacher->specialization ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">الحد الأقصى للحصص</label>
                                <p class="mt-1 text-gray-900 dark:text-white font-medium">{{ $staff->teacher->max_weekly_classes }} حصة/أسبوع</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column (Stats & Quick Info) --}}
            <div class="space-y-8">
                {{-- Quick Stats --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">إحصائيات سريعة</h4>
                    <div class="space-y-4">
                        {{-- Attendance Rate --}}
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500 dark:text-gray-400">نسبة الحضور (هذا الشهر)</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $this->attendanceRate }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $this->attendanceRate }}%"></div>
                            </div>
                        </div>
                        {{-- Leave Balance --}}
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500 dark:text-gray-400">رصيد الإجازات المتبقي</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $this->leaveBalanceSummary['remaining'] }} يوم</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $this->leaveBalanceSummary['percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- System Account --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">حساب النظام</h4>
                    @if($staff->user)
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $staff->user->username }}</p>
                                <p class="text-xs text-gray-500">آخر دخول: {{ $staff->user->last_login_at?->diffForHumans() ?? 'لم يدخل بعد' }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @php($roleLabels = config('access.role_labels', []))
                            @foreach($staff->user->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    {{ $roleLabels[$role->name] ?? $role->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                            <p class="text-sm text-gray-500 dark:text-gray-400">لا يوجد حساب مرتبط</p>
                            <button class="mt-2 text-sm text-indigo-600 font-medium hover:underline">إنشاء حساب</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Attendance Tab --}}
        <div x-show="activeTab === 'attendance'" x-cloak class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">سجل الحضور الأخير</h3>
                    <a href="#" class="text-sm text-indigo-600 font-medium hover:underline">عرض السجل الكامل</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">التاريخ</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">وقت الدخول</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">وقت الخروج</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">التأخير</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($staff->staffAttendances as $attendance)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $attendance->date->format('Y-m-d') }}
                                        <span class="text-gray-400 text-xs mr-1">({{ $attendance->date->translatedFormat('l') }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusLabels = ['present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'on_leave' => 'إجازة'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                               ($attendance->status === 'absent' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                                            {{ $statusLabels[$attendance->status] ?? $attendance->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $attendance->check_in?->format('H:i') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $attendance->check_out?->format('H:i') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($attendance->delay_minutes > 0)
                                            <span class="text-red-600 font-medium">{{ $attendance->delay_minutes }} دقيقة</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                        لا توجد سجلات حضور حديثة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Leaves Tab --}}
        <div x-show="activeTab === 'leaves'" x-cloak class="space-y-6">
            {{-- Balances Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($staff->leaveBalances as $balance)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $balance->leaveType->name }}</p>
                                <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $balance->remaining_days }} <span class="text-sm font-normal text-gray-500">يوم</span></h4>
                            </div>
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $balance->total_days > 0 ? ($balance->remaining_days / $balance->total_days) * 100 : 0 }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-400">من أصل {{ $balance->total_days }} يوم</p>
                    </div>
                @endforeach
            </div>
            
            {{-- Recent Requests --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">طلبات الإجازة الأخيرة</h3>
                </div>
                @if($staff->leaveRequests && $staff->leaveRequests->count() > 0)
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($staff->leaveRequests->take(10) as $request)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $request->leaveType?->name ?? 'غير محدد' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $request->start_date?->format('Y-m-d') }} → {{ $request->end_date?->format('Y-m-d') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $request->days_count ?? '-' }} يوم</span>
                                    @php
                                        $reqStatusColors = [
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        ];
                                        $reqStatusLabels = ['pending' => 'قيد الانتظار', 'approved' => 'موافق عليها', 'rejected' => 'مرفوضة'];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold {{ $reqStatusColors[$request->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $reqStatusLabels[$request->status] ?? $request->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">لا توجد طلبات إجازة حديثة</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Financial Tab --}}
        <div x-show="activeTab === 'financial'" x-cloak>
            <livewire:payroll.staff-financial-profile :staff="$staff" />
        </div>

        {{-- Access Control Tab --}}
        <div x-show="activeTab === 'access'" x-cloak>
            @if($staff->user)
                <livewire:admin.access.user-role-assigner :userId="$staff->user->id" />
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                هذا الموظف ليس لديه حساب مستخدم مرتبط. يجب إنشاء حساب أولاً لتعيين الصلاحيات.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
