<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        <!-- Modern Welcome Hero Section -->
        
        {{-- Flash Messages --}}
        <div class="relative overflow-hidden bg-gradient-purple-blue rounded-3xl shadow-2xl">
            <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
            <div class="relative z-10 px-8 py-10">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-bold text-white font-display mb-2 flex items-center gap-3">
                            <span class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                            دليل الطلاب
                        </h1>
                        <p class="text-white/90 text-lg">
                            إدارة شاملة لجميع بيانات الطلاب مع إحصائيات حية
                        </p>
                        <p class="text-white/80 mt-2">
                            نظام متكامل للبحث والفلترة المتقدمة
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('students.register') }}" wire:navigate
                            class="btn-gradient gradient-orange-pink hover:scale-105">
                            <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            إضافة طالب جديد
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards - Using Same Components as Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Total Students -->
            <div class="animate-fade-in" style="animation-delay: 0.1s">
                <x-stat-card label="إجمالي الطلاب" :value="$this->stats['total_students']" gradient="purple-blue" trend="neutral"
                    trendValue="طالب نشط">
                    <x-slot name="icon">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot>
                </x-stat-card>
            </div>

            <!-- Card 2: Attendance -->
            <div class="animate-fade-in" style="animation-delay: 0.2s">
                <x-stat-card label="الحضور اليوم" :value="$this->stats['attendance_today']" gradient="green-teal" trend="up"
                    trendValue="نسبة ممتازة">
                    <x-slot name="icon">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot>
                </x-stat-card>
            </div>

            <!-- Card 3: New Enrollments -->
            <div class="animate-fade-in" style="animation-delay: 0.3s">
                <x-stat-card label="الطلاب الجدد" :value="$this->stats['new_enrollments']" gradient="orange-pink" trend="up"
                    trendValue="هذا الشهر">
                    <x-slot name="icon">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </x-slot>
                </x-stat-card>
            </div>

            <!-- Card 4: Fee Alerts -->
            <div class="animate-fade-in" style="animation-delay: 0.4s">
                <x-stat-card label="تنبيهات مالية" :value="$this->stats['fee_alerts']" gradient="red-purple" trend="neutral"
                    trendValue="يحتاج متابعة">
                    <x-slot name="icon">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot>
                </x-stat-card>
            </div>
        </div>


        <!-- Smart Control Bar with Modern Design -->
        <div class="modern-card-elevated p-6">
            <div class="flex flex-col gap-6">
                <!-- Search Bar -->
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="block w-full pr-12 pl-4 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-base transition duration-150 ease-in-out"
                        placeholder="🔍 بحث شامل: الاسم، الهوية، الكود، رقم الجوال...">
                    <!-- Loading indicator for search -->
                    <div wire:loading wire:target="search" class="absolute left-4 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Cascading Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Academic Year Filter (Primary) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">السنة
                            الدراسية</label>
                        <select wire:model.live="selectedAcademicYear"
                            class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 rounded-xl dark:bg-gray-700 dark:text-gray-100 transition">
                            <option value="">كل السنوات</option>
                            @foreach ($this->academicYears as $year)
                                <option value="{{ $year->id }}">
                                    {{ $year->name }}
                                    @if ($year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active)
                                        ⭐
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @if (!$selectedAcademicYear)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">📌 اختر السنة أولاً</p>
                        @endif
                    </div>

                    <!-- Grade Filter (Cascading) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">الصف
                            الدراسي</label>
                        <select wire:model.live="selectedGrade"
                            class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 rounded-xl dark:bg-gray-700 dark:text-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$selectedAcademicYear ? 'disabled' : '' }}>
                            <option value="">كل الصفوف</option>
                            @foreach ($this->grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Section Filter (Cascading) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">الشعبة</label>
                        <select wire:model.live="selectedSection"
                            class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 rounded-xl dark:bg-gray-700 dark:text-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$selectedGrade || !$selectedAcademicYear ? 'disabled' : '' }}>
                            <option value="">كل الشعب</option>
                            @foreach ($this->sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">الحالة</label>
                        <select wire:model.live="selectedStatus"
                            class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 rounded-xl dark:bg-gray-700 dark:text-gray-100 transition">
                            <option value="">الكل</option>
                            <option value="active">نشط</option>
                            <option value="graduated">خريج</option>
                            <option value="withdrawn">منسحب</option>
                            <option value="suspended">موقوف</option>
                        </select>
                    </div>

                    <!-- Financial Status Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">الحالة
                            المالية</label>
                        <select wire:model.live="selectedFinancialStatus"
                            class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 rounded-xl dark:bg-gray-700 dark:text-gray-100 transition">
                            <option value="">الكل</option>
                            <option value="paid">خالص</option>
                            <option value="unpaid">عليه مستحقات</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Toolbar -->
        @if (count($selectedStudents) > 0)
            <div
                class="modern-card bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-300 dark:border-blue-700 p-5">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-12 h-12 bg-gradient-purple-blue text-white rounded-xl font-bold text-lg shadow-lg">
                            {{ count($selectedStudents) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                تم تحديد {{ count($selectedStudents) }} طالب
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">يمكنك تنفيذ إجراءات جماعية</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="exportSelected"
                            class="btn-modern bg-white hover:bg-gray-50 text-gray-700 border border-gray-300">
                            <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            تصدير
                        </button>
                        <button wire:click="sendSmsToGuardians" class="btn-gradient gradient-green-teal">
                            <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            إرسال SMS
                        </button>
                        <button wire:click="deleteSelected"
                            wire:confirm="هل أنت متأكد من حذف الطلاب المحددين؟ سيتم تخطي الطلاب الذين لديهم بيانات مرتبطة."
                            class="btn-modern bg-red-50 hover:bg-red-100 text-red-700 border border-red-200">
                            <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            حذف المحدد
                        </button>
                        <button wire:click="$set('selectedStudents', [])"
                            class="btn-modern bg-gray-100 hover:bg-gray-200 text-gray-700">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Data Grid with Modern Card Design -->
        <div class="modern-card-elevated overflow-hidden relative">
            <!-- Loading Overlay -->
            <div wire:loading.flex
                wire:target="selectedAcademicYear,selectedGrade,selectedSection,selectedStatus,selectedFinancialStatus,search"
                class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 z-20 items-center justify-center backdrop-blur-sm">
                <div class="flex flex-col items-center gap-3">
                    <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">جاري التحميل...</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                الطالب
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                المرحلة الدراسية
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                ولي الأمر
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                الحالة المالية
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                الحالة
                            </th>
                            <th scope="col" class="relative px-6 py-3 whitespace-nowrap">
                                <span class="sr-only">إجراءات</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-surface divide-y divide-border">
                        @forelse($students as $student)
                            <tr class="hover:bg-background/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600"
                                                src="{{ $student->profile_photo_url }}" alt="">
                                        </div>
                                        <div class="mr-4">
                                            <div class="text-sm font-medium text-foreground">
                                                {{ $student->full_name_ar }}
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ $student->admission_number }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-foreground">
                                        {{ $student->currentGrade?->name ?? '-' }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ $student->currentClassSection?->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($student->guardians->isNotEmpty())
                                        <div class="flex items-center text-sm text-foreground">
                                            {{ $student->guardians->first()->first_name }}
                                            {{ $student->guardians->first()->last_name }}
                                            <a href="tel:{{ $student->guardians->first()->phone }}"
                                                class="mr-2 text-primary hover:text-primary/80">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-sm text-muted-foreground">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $financial = $financialStatuses[$student->id];
                                    @endphp
                                    <x-student.badge 
                                        :status="$financial['status'] ?? ($financial['amount'] > 0 ? 'unpaid' : 'paid')" 
                                        :label="$financial['label'] . ($financial['amount'] > 0 ? ' (' . number_format($financial['amount']) . ' ريال)' : '')"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-student.badge :status="$student->status" :label="$student->status->label()" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open"
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div x-show="open" @click.away="open = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute left-0 mt-2 w-56 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600 z-30"
                                            style="display: none;">
                                            <div class="py-1">
                                                <a href="{{ route('students.show', $student->id) }}" wire:navigate
                                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                                    <span class="text-lg">👁️</span>
                                                    <span>عرض الملف الكامل</span>
                                                </a>
                                                <a href="#"
                                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                                    <span class="text-lg">✏️</span>
                                                    <span>تعديل البيانات</span>
                                                </a>
                                                <a href="#"
                                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                                    <span class="text-lg">💰</span>
                                                    <span>كشف الحساب</span>
                                                </a>
                                                <a href="#"
                                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                                    <span class="text-lg">📝</span>
                                                    <span>سجل الحضور</span>
                                                </a>
                                                <hr class="my-1 border-gray-200 dark:border-gray-600">
                                                <button wire:click="delete({{ $student->id }})"
                                                    wire:confirm="هل أنت متأكد من حذف هذا الطالب؟ سيتم حذف جميع البيانات المرتبطة به."
                                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                    <span class="text-lg">🗑️</span>
                                                    <span>حذف الطالب</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div
                                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-2">
                                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">لا توجد نتائج
                                            مطابقة</h3>
                                        <p class="text-gray-500 dark:text-gray-400 max-w-sm">
                                            لم نتمكن من العثور على أي طلاب يطابقون معايير البحث الخاصة بك. حاول تغيير
                                            الكلمات المفتاحية أو إزالة الفلاتر.
                                        </p>
                                        @if ($search || $selectedGrade || $selectedSection || $selectedStatus || $selectedFinancialStatus)
                                            <button
                                                wire:click="$set('search', ''); $set('selectedGrade', ''); $set('selectedSection', ''); $set('selectedStatus', ''); $set('selectedFinancialStatus', '');"
                                                class="px-4 py-2 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 rounded-lg text-sm font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                                                مسح جميع الفلاتر
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $students->links() }}
            </div>
        </div>
    </div>
