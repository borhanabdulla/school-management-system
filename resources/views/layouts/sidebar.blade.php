<div x-data="{
    open: false,
    activeSubmenu: '{{ request()->routeIs('academic-years.*') ||
    request()->routeIs('terms.*') ||
    request()->routeIs('structure.*') ||
    request()->routeIs('class-sections.*') ||
    request()->routeIs('subject-manager.*') ||
    request()->routeIs('academic-directory.*') ||
    request()->routeIs('course-offerings.*') ||
    request()->routeIs('timetable-templates.*') ||
    request()->routeIs('timetable.*') ||
    request()->routeIs('academic.calendar') ||
    request()->routeIs('grading.structure.*')
        ? 'academic-years'
        : (request()->routeIs('students.*')
            ? 'students'
            : (request()->routeIs('teachers.*') || request()->routeIs('teacher.*') || request()->routeIs('grading.gradebook.*')
                ? 'teachers'
                : (request()->routeIs('guardians.*')
                    ? 'guardians'
                    : (request()->routeIs('hr.*')
                        ? 'hr'
                        : (request()->routeIs('finance.*')
                            ? 'finance'
                            : (request()->routeIs('promotion.*')
                                ? 'promotion'
                                : '')))))) }}',
    toggleSubmenu(name) {
        this.activeSubmenu = this.activeSubmenu === name ? '' : name;
    }
}" @toggle-sidebar.window="open = !open">


    <div id="sidebar-overlay" class="sidebar-overlay" :class="{ 'active': open }" @click="open = false"></div>
    <div id="sidebar" class="sidebar" :class="{ 'active': open }">
        <!-- Modern Sidebar Header with Logo -->
        <div class="sidebar-header">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center border border-primary/20">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
            <h2 class="text-xl font-bold text-foreground">نظام المدرسة</h2>
            <p class="text-xs text-muted-foreground mt-1">إدارة شاملة</p>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            <!-- Dashboard -->
            <div class="sidebar-nav-item">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>الرئيسية</span>
                </a>
            </div>

            <!-- ============================================================ -->
            <!-- بداية التعديل: الإدارة الأكاديمية (شاملة الروابط الجديدة) -->
            <!-- ============================================================ -->
            @canany(['curriculum.manage', 'classes.manage', 'timetable.manage', 'close.year', 'grading.manage_settings'])
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('academic-years')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'academic-years' }">

                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>الإدارة الأكاديمية</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'academic-years' }"
                    id="academic-years-submenu">

                    {{-- 1. السنوات الدراسية --}}
                    <li>
                        <a href="{{ route('academic-years.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('academic-years.*') ? 'active' : '' }}">
                            السنوات الدراسية
                        </a>
                    </li>

                    {{-- 2. الفصول الدراسية --}}
                    <li>
                        <a href="{{ route('terms.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('terms.*') ? 'active' : '' }}">
                            الفصول الدراسية (Terms)
                        </a>
                    </li>

                    {{-- 3. المراحل والصفوف --}}
                    <li>
                        <a href="{{ route('structure.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('structure.*') ? 'active' : '' }}">
                            المراحل والصفوف
                        </a>
                    </li>

                    {{-- 4. الشعب الدراسية --}}
                    <li>
                        <a href="{{ route('class-sections.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('class-sections.*') ? 'active' : '' }}">
                            الشعب الدراسية
                        </a>
                    </li>

                    {{-- 5. المواد والمناهج --}}
                    <li>
                        <a href="{{ route('subject-manager.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('subject-manager.*') ? 'active' : '' }}">
                            المواد والمناهج
                        </a>
                    </li>

                    {{-- 6. الدليل الأكاديمي --}}
                    <li>
                        <a href="{{ route('academic-directory.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('academic-directory.*') ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span>📚 الدليل الأكاديمي</span>
                            </span>
                        </a>
                    </li>

                    {{-- 7. تعيين المواد --}}
                    <li>
                        <a href="{{ route('course-offerings.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('course-offerings.*') ? 'active' : '' }}">
                            تعيين المواد
                        </a>
                    </li>

                    {{-- 8. قوالب الدوام --}}
                    <li>
                        <a href="{{ route('timetable-templates.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('timetable-templates.*') ? 'active' : '' }}">
                            ⏱️ قوالب الدوام
                        </a>
                    </li>

                    {{-- 9. الجدول الدراسي --}}
                    <li>
                        <a href="{{ route('timetable.builder') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('timetable.*') ? 'active' : '' }}">
                            الجدول الدراسي
                        </a>
                    </li>

                    {{-- 10. التقويم والعطل --}}
                    <li>
                        <a href="{{ route('academic.calendar') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('academic.calendar') ? 'active' : '' }}">
                            التقويم والعطل
                        </a>
                    </li>

                    {{-- 11. إعدادات الدرجات --}}
                    <li>
                        <a href="{{ route('grading.settings') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('grading.settings') ? 'active' : '' }}">
                            📊 قوالب الدرجات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('grading.subjects') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('grading.subjects') ? 'active' : '' }}">
                            📚 تهيئة المواد
                        </a>
                    </li>
                </ul>
            </div>
            @endcanany
            {{-- نهاية الإدارة الأكاديمية --}}


            <!-- Students with Submenu -->
            @can('students.view')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('students')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'students' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>الطلاب</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'students' }" id="students-submenu">
                    <li>
                        <a href="{{ route('students.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('students.index') ? 'active' : '' }}">
                            قائمة الطلاب
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('students.register') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('students.register') ? 'active' : '' }}">
                            إضافة طالب
                        </a>
                    </li>
                    <li><a href="#" class="sidebar-submenu-link">بيانات الطلاب</a></li>
                    <li><a href="#" class="sidebar-submenu-link">التحويلات</a></li>
                    <li>
                        <a href="{{ route('student.homeworks.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('student.homeworks.*') ? 'active' : '' }}">
                            📚 واجباتي
                        </a>
                    </li>
                </ul>
            </div>
            @endcan

            <!-- Teachers with Submenu -->
            @canany(['staff.view', 'teacher.dashboard', 'grading.view_gradebook'])
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('teachers')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'teachers' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>المعلمين</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'teachers' }" id="teachers-submenu">
                    @can('teacher.dashboard')
                    <li>
                        <a href="{{ route('teacher.dashboard') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                            📊 لوحة المعلم
                        </a>
                    </li>
                    @endcan
                    @can('staff.view')
                    <li>
                        <a href="{{ route('teachers.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('teachers.index') ? 'active' : '' }}">
                            قائمة المعلمين
                        </a>
                    </li>
                    @endcan
                    @can('staff.create')
                    <li>
                        <a href="{{ route('teachers.create') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('teachers.create') ? 'active' : '' }}">
                            إضافة معلم
                        </a>
                    </li>
                    @endcan
                    @can('grading.view_gradebook')
                    <li>
                        <a href="{{ route('grading.gradebooks') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('grading.gradebooks') || request()->routeIs('grading.gradebook.*') ? 'active' : '' }}">
                            📝 دفتر الدرجات
                        </a>
                    </li>
                    @endcan
                    @can('teacher.timetable')
                    <li>
                        <a href="{{ route('teacher.timetable') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('teacher.timetable') ? 'active' : '' }}">
                            📅 جدولي الأسبوعي
                        </a>
                    </li>
                    @endcan
                    @can('attendance.view')
                    <li>
                        <a href="{{ route('teacher.attendance.report') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('teacher.attendance.report') ? 'active' : '' }}">
                            📊 تقرير الحضور
                        </a>
                    </li>
                    @endcan
                    @can('teacher.homework')
                    <li>
                        <a href="{{ route('teacher.homework.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('teacher.homework.*') || request()->routeIs('grading.homework.*') ? 'active' : '' }}">
                            📚 الواجبات
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

            <!-- HR - شؤون الموظفين -->
            @canany(['staff.view', 'staff.create', 'attendance.manage', 'leave.request', 'leaves.approve'])
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('hr')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'hr' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>شؤون الموظفين</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'hr' }" id="hr-submenu">
                    <li>
                        <a href="{{ route('hr.dashboard') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                            📊 لوحة التحكم
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.staff.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.staff.*') ? 'active' : '' }}">
                            👥 دليل الموظفين
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.staff.create') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.staff.create') ? 'active' : '' }}">
                            ➕ إضافة موظف
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.attendance.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.attendance.*') ? 'active' : '' }}">
                            📋 سجل الحضور
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.shifts.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.shifts.*') ? 'active' : '' }}">
                            ⏰ فترات الدوام
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.leave.dashboard') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.leave.dashboard') ? 'active' : '' }}">
                            📊 لوحة إجازاتي
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.leave.types') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.leave.types') ? 'active' : '' }}">
                            ⚙️ أنواع الإجازات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.leave.request') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.leave.request') ? 'active' : '' }}">
                            📝 طلب إجازة
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hr.leave.approvals') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('hr.leave.approvals') ? 'active' : '' }}">
                            ✅ الموافقات
                        </a>
                    </li>
                </ul>
            </div>
            @endcanany

            <!-- Payroll - الرواتب -->
            @can('payroll.manage')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('payroll')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'payroll' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>الرواتب</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'payroll' }" id="payroll-submenu">
                    <li>
                        <a href="{{ route('payroll.dashboard') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('payroll.dashboard') ? 'active' : '' }}">
                            📊 اللوحة المالية
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.contracts.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('payroll.contracts.*') ? 'active' : '' }}">
                            📜 العقود
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.loans.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('payroll.loans.*') ? 'active' : '' }}">
                            💸 السلف
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.batches.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('payroll.batches.*') ? 'active' : '' }}">
                            💰 دفعات الرواتب
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.salary-components.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('payroll.salary-components.*') ? 'active' : '' }}">
                            📋 بنود الراتب
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.settings') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('payroll.settings') ? 'active' : '' }}">
                            ⚙️ الإعدادات
                        </a>
                    </li>
                </ul>
            </div>
            @endcan

            <!-- Finance - المالية -->
            @can('finance.view')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('finance')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'finance' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>المالية</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'finance' }" id="finance-submenu">
                    <!-- Invoice List -->
                    <li>
                        <a href="{{ route('finance.invoices.index') }}" wire:navigate 
                           class="sidebar-submenu-link {{ request()->routeIs('finance.invoices.*') ? 'active' : '' }}">
                             🧾 الفواتير
                        </a>
                    </li>
                </ul>
            </div>
            @endcan

            <!-- Guardians with Submenu -->
            @can('guardians.view')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('guardians')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'guardians' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>أولياء الأمور</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'guardians' }" id="guardians-submenu">
                    <li>
                        <a href="{{ route('guardians.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('guardians.index') ? 'active' : '' }}">
                            قائمة أولياء الأمور
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('guardians.create') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('guardians.create') ? 'active' : '' }}">
                            إضافة ولي أمر
                        </a>
                    </li>
                </ul>
            </div>
            @endcan

            <!-- Classes with Submenu -->
            @can('classes.manage')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('classes')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'classes' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>الفصول (إدارة سريعة)</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'classes' }" id="classes-submenu">
                    <li><a href="#" class="sidebar-submenu-link">جميع الفصول</a></li>
                    <li><a href="#" class="sidebar-submenu-link">توزيع الطلاب</a></li>
                    <li><a href="#" class="sidebar-submenu-link">القاعات الدراسية</a></li>
                </ul>
            </div>
            @endcan

            <!-- Subjects with Submenu -->
            @can('curriculum.manage')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('subjects')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'subjects' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>المواد</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'subjects' }" id="subjects-submenu">
                    <li><a href="#" class="sidebar-submenu-link">قائمة المواد</a></li>
                    <li><a href="#" class="sidebar-submenu-link">إضافة مادة</a></li>
                    <li><a href="#" class="sidebar-submenu-link">توزيع المواد</a></li>
                    <li><a href="#" class="sidebar-submenu-link">المناهج الدراسية</a></li>
                </ul>
            </div>
            @endcan

            <!-- Exams with Submenu -->
            @can('marks.override')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('exams')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'exams' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>الامتحانات والكنترول</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'exams' }" id="exams-submenu">
                    <li>
                        <a href="{{ route('control.dashboard') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('control.dashboard') ? 'active' : '' }}">
                            🎛️ لوحة الكنترول
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('control.grading') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('control.grading') ? 'active' : '' }}">
                            🔐 الرصد الأعمى
                        </a>
                    </li>
                    <li><a href="#" class="sidebar-submenu-link">📅 جدول الامتحانات</a></li>
                    <li><a href="#" class="sidebar-submenu-link">📊 النتائج</a></li>
                </ul>
            </div>
            @endcan

            <!-- Promotion - نظام الترحيل -->
            @can('students.promote')
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('promotion')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'promotion' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z" />
                        </svg>
                        <span>الترحيل والنتائج</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'promotion' }" id="promotion-submenu">
                    <li>
                        <a href="{{ route('promotion.annual-results') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('promotion.annual-results') ? 'active' : '' }}">
                            📊 النتائج السنوية
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('promotion.manage') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('promotion.manage') ? 'active' : '' }}">
                            🔄 إدارة الترحيل
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('promotion.settings') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('promotion.settings') ? 'active' : '' }}">
                            ⚙️ إعدادات الترحيل
                        </a>
                    </li>
                </ul>
            </div>
            @endcan

            <!-- Attendance with Submenu -->
            @canany(['attendance.manage', 'attendance.view', 'attendance.take'])
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('attendance')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'attendance' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span>الحضور والغياب</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'attendance' }"
                    id="attendance-submenu">
                    <li><a href="#" class="sidebar-submenu-link">تسجيل الحضور</a></li>
                    <li><a href="#" class="sidebar-submenu-link">تقارير الحضور</a></li>
                    <li><a href="#" class="sidebar-submenu-link">الإحصائيات</a></li>
                    <li><a href="#" class="sidebar-submenu-link">الإجازات</a></li>
                    <li>
                        <a href="{{ route('attendance.settings') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('attendance.settings') ? 'active' : '' }}">
                            ⚙️ إعدادات الحضور
                        </a>
                    </li>
                </ul>
            </div>
            @endcanany

            <!-- Announcements -->
            <div class="sidebar-nav-item">
                <a href="#" class="sidebar-nav-link">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <span>الإعلانات</span>
                    <span class="badge-notify">3</span>
                </a>
            </div>

            <!-- Events -->
            <div class="sidebar-nav-item">
                <a href="#" class="sidebar-nav-link">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>الفعاليات</span>
                </a>
            </div>

            <!-- Divider -->
            <div class="sidebar-divider">
                <span>الإعدادات</span>
            </div>

            <!-- Settings with Submenu -->
            @canany(['settings.edit', 'roles.manage', 'sensitive.manage'])
            <div class="sidebar-nav-item">
                <button @click="toggleSubmenu('settings')" class="sidebar-nav-link sidebar-dropdown-toggle"
                    :class="{ 'active open': activeSubmenu === 'settings' }">
                    <div class="flex items-center flex-1">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>الإعدادات</span>
                    </div>
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul class="sidebar-submenu" :class="{ 'open': activeSubmenu === 'settings' }" id="settings-submenu">
                    <li><a href="#" class="sidebar-submenu-link">إعدادات عامة</a></li>
                    <li><a href="#" class="sidebar-submenu-link">المستخدمين</a></li>
                    <li>
                        <a href="{{ route('admin.access.roles.index') }}" wire:navigate
                            class="sidebar-submenu-link {{ request()->routeIs('admin.access.roles.*') ? 'active' : '' }}">
                            الصلاحيات والأدوار
                        </a>
                    </li>
                    @can('sensitive.manage')
                        <li>
                            <a href="{{ route('security.sensitive-access') }}" wire:navigate
                                class="sidebar-submenu-link {{ request()->routeIs('security.sensitive-access') ? 'active' : '' }}">
                                رمز الأمان الحساس
                            </a>
                        </li>
                    @endcan
                    <li><a href="#" class="sidebar-submenu-link">النسخ الاحتياطي</a></li>
                </ul>
            </div>
            @endcanany
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="flex items-center gap-3 p-3 bg-surface rounded-xl border border-border">
                <div
                    class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold border border-primary/20">
                    {{ substr(Auth::user()->username ?? 'A', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-foreground truncate">{{ Auth::user()->username ?? 'المستخدم' }}
                    </p>
                    <p class="text-xs text-muted-foreground truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
        </div>

    </div>
