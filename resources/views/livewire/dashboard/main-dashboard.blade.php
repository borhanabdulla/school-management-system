@php
    $barMap = $colorMaps['bar'] ?? [];
    $dotMap = $colorMaps['dot'] ?? [];
    $pillMap = $colorMaps['pill'] ?? [];
@endphp

<div class="py-6" wire:loading.class="opacity-60" wire:target="academicYearId,termId,gradeId,range,setFocus">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950/80 via-slate-900/80 to-indigo-900/60 p-6 sm:p-8 mb-10">
            <div class="absolute -top-24 -right-16 h-64 w-64 rounded-full bg-indigo-500/30 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-emerald-500/20 blur-3xl"></div>
            <div class="absolute top-16 right-1/3 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl"></div>

            <div class="relative z-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            نبض المدرسة
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-display font-semibold text-white">لوحة القيادة التفاعلية</h1>
                        <p class="text-sm text-white/70 max-w-xl">
                            كل شيء في مكان واحد: الأداء الأكاديمي، الحضور، التحصيل المالي، والتنبيهات الحرجة.
                        </p>
                        <div class="flex flex-wrap gap-3 text-xs">
                            <div class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-white/80">
                                السنة: {{ $allYears ? 'كل السنوات' : ($this->selectedYear?->name ?? 'غير محددة') }}
                            </div>
                            <div class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-white/80">
                                الفصل: {{ $selectedTerm?->name ?? 'كل الفصول' }}
                            </div>
                            <div class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-white/80">
                                التحصيل: {{ $stats['finance_paid_ratio'] }}%
                            </div>
                            <div class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-white/80">
                                الحضور: {{ $stats['attendance_rate_today'] }}%
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                        <div class="rounded-2xl bg-white/10 p-4 text-white/90">
                            <div class="text-xs text-white/70">طلاب نشطون</div>
                            <div class="text-2xl font-semibold">{{ $stats['active_students'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 text-white/90">
                            <div class="text-xs text-white/70">معلمون</div>
                            <div class="text-2xl font-semibold">{{ $stats['teachers'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 text-white/90">
                            <div class="text-xs text-white/70">إنذارات الجاهزية</div>
                            <div class="text-2xl font-semibold">{{ $stats['readiness_blocking'] + $stats['readiness_warnings'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 text-white/90">
                            <div class="text-xs text-white/70">الإجازات المعلقة</div>
                            <div class="text-2xl font-semibold">{{ $stats['leave_pending'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('students.register') }}" class="px-4 py-2 rounded-lg bg-white text-slate-900 text-sm font-semibold hover:bg-slate-100 transition">تسجيل طالب جديد</a>
                    <a href="{{ route('teachers.create') }}" class="px-4 py-2 rounded-lg bg-indigo-500 text-white text-sm font-semibold hover:bg-indigo-400 transition">إضافة معلم</a>
                    <a href="{{ route('academic-years.index') }}" class="px-4 py-2 rounded-lg border border-white/20 text-white text-sm font-semibold hover:bg-white/10 transition">إدارة السنوات الدراسية</a>
                </div>
            </div>
        </div>

        <div class="modern-card-elevated p-5 mb-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-slate-300 mb-1">السنة الدراسية</label>
                    <select wire:model.live.debounce.300ms="academicYearId"
                        class="w-full rounded-lg border-gray-200 dark:border-border dark:bg-surface dark:text-white text-sm">
                        <option value="">كل السنوات</option>
                        @foreach ($this->academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                    <label class="mt-2 inline-flex items-center gap-2 text-[11px] text-gray-500 dark:text-slate-400">
                        <input type="checkbox" wire:model.live="allYears" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        عرض كل السنوات
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-slate-300 mb-1">الفصل الدراسي</label>
                    <select wire:model.live.debounce.300ms="termId"
                        class="w-full rounded-lg border-gray-200 dark:border-border dark:bg-surface dark:text-white text-sm"
                        {{ $this->terms->isEmpty() ? 'disabled' : '' }}>
                        <option value="">كل الفصول</option>
                        @foreach ($this->terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-slate-300 mb-1">الصف الدراسي</label>
                    <select wire:model.live.debounce.300ms="gradeId"
                        class="w-full rounded-lg border-gray-200 dark:border-border dark:bg-surface dark:text-white text-sm">
                        <option value="">كل الصفوف</option>
                        @foreach ($this->grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-slate-300 mb-1">الفترة</label>
                    <select wire:model.live.debounce.300ms="range"
                        class="w-full rounded-lg border-gray-200 dark:border-border dark:bg-surface dark:text-white text-sm">
                        <option value="7">آخر 7 أيام</option>
                        <option value="30">آخر 30 يوم</option>
                        <option value="90">آخر 90 يوم</option>
                    </select>
                </div>
            </div>
        </div>

        <div wire:loading.delay wire:target="academicYearId,termId,gradeId,range,setFocus" class="mb-6">
            <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                <div class="h-full w-1/3 bg-indigo-500/80 animate-pulse"></div>
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-slate-400">جاري تحديث البيانات...</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div>
                <x-stat-card label="إجمالي الطلاب" :value="$stats['total_students']" gradient="purple-blue" trend="neutral" trendValue="المقيدون">
                    <x-slot name="icon">
                        <x-illustrations.students size="lg" />
                    </x-slot>
                </x-stat-card>
            </div>

            <button type="button" class="text-left w-full focus:outline-none" wire:click="openAttendanceDropdown('{{ now()->toDateString() }}')">
                <x-stat-card label="الحضور اليوم" :value="$stats['attendance_today']" gradient="green-teal" trend="neutral" trendValue="{{ $stats['attendance_rate_today'] }}%">
                    <x-slot name="icon">
                        <x-illustrations.attendance size="lg" />
                    </x-slot>
                </x-stat-card>
            </button>

            <button type="button" class="text-left w-full focus:outline-none" wire:click="openDrawer('finance')">
                <x-stat-card label="التحصيل المالي" :value="number_format($finance['paid'], 0)" gradient="orange-pink" trend="neutral" trendValue="{{ $finance['paid_ratio'] }}%">
                    <x-slot name="icon">
                        <x-illustrations.finance size="lg" />
                    </x-slot>
                </x-stat-card>
            </button>

            <button type="button" class="text-left w-full focus:outline-none" wire:click="openDrawer('health')">
                <x-stat-card label="جاهزية الإغلاق" :value="$stats['readiness_blocking']" gradient="red-purple" trend="neutral" trendValue="{{ $stats['readiness_warnings'] }} تنبيه">
                    <x-slot name="icon">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                    </x-slot>
                </x-stat-card>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="modern-card-elevated p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">مؤشر صحة النظام</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 rounded-full {{ $pillMap[$healthScore['color']] ?? 'bg-slate-500/15 text-slate-300' }}">
                            {{ $healthScore['label'] }}
                        </span>
                        <button type="button" class="text-xs text-indigo-500 hover:text-indigo-400" wire:click="openDrawer('health')">تفاصيل</button>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="relative h-28 w-28 rounded-full" style="background: {{ $healthRing }};">
                        <div class="absolute inset-3 rounded-full bg-white dark:bg-surface flex flex-col items-center justify-center">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $healthScore['score'] }}%</div>
                            <div class="text-[10px] text-gray-500 dark:text-slate-400">جاهزية</div>
                        </div>
                    </div>
                    <div class="space-y-3 text-sm text-gray-600 dark:text-slate-300">
                        <div class="flex items-center justify-between gap-4">
                            <span>الحضور اليوم</span>
                            <span class="font-semibold">{{ $stats['attendance_rate_today'] }}%</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span>التحصيل المالي</span>
                            <span class="font-semibold">{{ $finance['paid_ratio'] }}%</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span>تنبيهات حرجة</span>
                            <span class="font-semibold text-red-500">{{ $stats['readiness_blocking'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-card-elevated p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">مسارات النمو</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400">قراءة سريعة لاتجاهات الأداء خلال الفترة المختارة</p>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-slate-400">آخر {{ $range }} يوم</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-white/70 dark:bg-slate-900/50">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">متوسط الحضور</p>
                                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $attendanceTrend['avg_rate'] }}%</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="text-[11px] {{ $attendanceCompare ? 'text-indigo-400' : 'text-slate-500 dark:text-slate-300' }} hover:text-indigo-400"
                                    wire:click="$toggle('attendanceCompare')">
                                    قارن
                                </button>
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-500/15 text-emerald-300">أعلى {{ $attendanceTrend['max_rate'] }}%</span>
                            </div>
                        </div>
                        <div class="h-16">
                            <livewire:livewire-line-chart :line-chart-model="$attendanceLineChart"
                                key="{{ $attendanceLineChart->reactiveKey() }}" />
                        </div>
                    </div>

                    <div class="relative rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-white/70 dark:bg-slate-900/50">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">الانضمامات الجديدة</p>
                                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $enrollmentTrend['sum'] }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="text-[11px] {{ $enrollmentCompare ? 'text-indigo-400' : 'text-slate-500 dark:text-slate-300' }} hover:text-indigo-400"
                                    wire:click="$toggle('enrollmentCompare')">
                                    قارن
                                </button>
                                <span class="text-xs px-2 py-1 rounded-full {{ $enrollmentTrend['direction'] === 'up' ? 'bg-emerald-500/15 text-emerald-300' : ($enrollmentTrend['direction'] === 'down' ? 'bg-red-500/15 text-red-300' : 'bg-slate-500/15 text-slate-300') }}">
                                    {{ $enrollmentTrend['direction'] === 'down' ? '-' : '+' }}{{ $enrollmentTrend['delta_percent'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="h-16">
                            <livewire:livewire-line-chart :line-chart-model="$enrollmentLineChart"
                                key="{{ $enrollmentLineChart->reactiveKey() }}" />
                        </div>
                    </div>

                    <div class="relative rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-white/70 dark:bg-slate-900/50">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">التحصيل اليومي</p>
                                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($financeTrend['sum'], 0) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="text-[11px] {{ $financeCompare ? 'text-indigo-400' : 'text-slate-500 dark:text-slate-300' }} hover:text-indigo-400"
                                    wire:click="$toggle('financeCompare')">
                                    قارن
                                </button>
                                <span class="text-xs px-2 py-1 rounded-full {{ $financeTrend['direction'] === 'up' ? 'bg-emerald-500/15 text-emerald-300' : ($financeTrend['direction'] === 'down' ? 'bg-red-500/15 text-red-300' : 'bg-slate-500/15 text-slate-300') }}">
                                    {{ $financeTrend['direction'] === 'down' ? '-' : '+' }}{{ $financeTrend['delta_percent'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="h-16">
                            <livewire:livewire-line-chart :line-chart-model="$financeLineChart"
                                key="{{ $financeLineChart->reactiveKey() }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="modern-card-elevated p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">طلاب جدد</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $stats['new_enrollments'] }}</p>
            </div>
            <div class="modern-card-elevated p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">الموظفون النشطون</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_staff'] }}</p>
            </div>
            <div class="modern-card-elevated p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">فواتير مفتوحة</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $finance['outstanding_count'] }}</p>
            </div>
            <div class="modern-card-elevated p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">الحضور الكلي اليوم</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $stats['attendance_total_today'] }}</p>
            </div>
        </div>

        @if ($this->selectedYear)
            <div class="mb-10">
                <x-academic.year-progress :selectedYear="$this->selectedYear" />
            </div>
        @endif

        <div class="modern-card-elevated p-6 mb-10">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">خريطة النظام</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400">تصوير بصري لامتداد البيانات عبر وحدات النظام</p>
                </div>
                <span class="text-xs text-gray-500 dark:text-slate-400">تحديث مباشر</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">الطلاب</p>
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['students'] }}</p>
                </div>
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">المعلمون</p>
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z\" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5c-2.28 0-4.382-.63-6.16-1.722L12 14z\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['teachers'] }}</p>
                </div>
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">الشعب</p>
                        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['sections'] }}</p>
                </div>
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">المواد</p>
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2\" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['subjects'] }}</p>
                </div>
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">الفواتير</p>
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6M6 4h12a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['invoices'] }}</p>
                </div>
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">الفعاليات</p>
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['events'] }}</p>
                </div>
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">السنوات</p>
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['years'] }}</p>
                </div>
                <div class="group rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-gradient-to-br from-white/70 to-slate-50 dark:from-slate-900/60 dark:to-slate-800/40">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-slate-400">الفصول</p>
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10\" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $systemSnapshot['terms'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="modern-card-elevated p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">توزيع الطلاب حسب الجنس</h3>
                <div class="flex items-center gap-6">
                    <div class="relative h-32 w-32 rounded-full"
                        style="background: conic-gradient(#6366f1 0 {{ $genderBreakdown['male_ratio'] }}%, #f43f5e {{ $genderBreakdown['male_ratio'] }}% {{ $genderBreakdown['male_ratio'] + $genderBreakdown['female_ratio'] }}%, rgba(148, 163, 184, 0.2) {{ $genderBreakdown['male_ratio'] + $genderBreakdown['female_ratio'] }}% 100%);">
                        <div class="absolute inset-3 rounded-full bg-white dark:bg-surface flex flex-col items-center justify-center">
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ $genderBreakdown['total'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400">طالب/ة</div>
                        </div>
                    </div>
                    <div class="space-y-3 text-sm">
                        @foreach ($genderBreakdown['items'] as $item)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $dotMap[$item['color']] ?? 'bg-slate-400' }}"></span>
                                    <span class="text-gray-600 dark:text-slate-300">{{ $item['label'] }}</span>
                                </div>
                                <div class="text-gray-900 dark:text-white font-semibold">{{ $item['ratio'] }}%</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modern-card-elevated p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">الفعاليات القادمة</h3>
                <div class="space-y-3">
                    @forelse ($upcomingEvents as $event)
                        <div class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 dark:border-slate-700 p-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">{{ $event['start'] }} @if($event['end'] && $event['end'] !== $event['start']) — {{ $event['end'] }} @endif</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $pillMap[$event['color']] ?? 'bg-slate-500/15 text-slate-300' }}">
                                {{ $event['type'] }}
                            </span>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-slate-300">لا توجد فعاليات قادمة.</div>
                    @endforelse
                </div>
            </div>

            <div class="modern-card-elevated p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">مؤشر الجاهزية التشغيلية</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-slate-300">
                        <span>الحضور اليوم</span>
                        <span class="font-semibold">{{ $stats['attendance_rate_today'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $stats['attendance_rate_today'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-slate-300">
                        <span>التحصيل المالي</span>
                        <span class="font-semibold">{{ $finance['paid_ratio'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $finance['paid_ratio'] }}%"></div>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-slate-300">تنبيهات الجاهزية</span>
                            <span class="text-amber-500 font-semibold">{{ $stats['readiness_warnings'] }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-gray-600 dark:text-slate-300">مشاكل مانعة</span>
                            <span class="text-red-500 font-semibold">{{ $stats['readiness_blocking'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div wire:init="loadHeatmap">
            @if ($this->loadHeatmap)
                @include('livewire.dashboard.partials.attendance-heatmap')
            @else
                <div class="modern-card-elevated p-6 mb-10 shadow-none dark:shadow-none">
                    <div class="h-5 w-48 rounded bg-slate-200 dark:bg-slate-700/60 mb-4"></div>
                    <div class="grid grid-cols-7 gap-2">
                        @for ($i = 0; $i < 28; $i++)
                            <div class="h-8 rounded bg-slate-200 dark:bg-slate-700/60"></div>
                        @endfor
                    </div>
                </div>
            @endif
        </div>

        @include('livewire.dashboard.partials.attendance-dropdown')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div wire:init="loadAging">
                @if ($this->loadAging)
                    @include('livewire.dashboard.partials.finance-aging')
                @else
                    <div class="modern-card-elevated p-6 shadow-none dark:shadow-none">
                        <div class="h-5 w-40 rounded bg-slate-200 dark:bg-slate-700/60 mb-4"></div>
                        <div class="space-y-3">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="h-3 rounded bg-slate-200 dark:bg-slate-700/60"></div>
                            @endfor
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-2" wire:init="loadPareto">
                @if ($this->loadPareto)
                    @include('livewire.dashboard.partials.aging-pareto')
                @else
                    <div class="modern-card-elevated p-6 shadow-none dark:shadow-none">
                        <div class="h-5 w-56 rounded bg-slate-200 dark:bg-slate-700/60 mb-6"></div>
                        <div class="space-y-4">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="h-3 rounded bg-slate-200 dark:bg-slate-700/60"></div>
                            @endfor
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @include('livewire.dashboard.partials.focus-panel')

        @include('livewire.dashboard.partials.people-distributions')

        @include('livewire.dashboard.partials.alerts-insights')

        @include('livewire.dashboard.partials.quick-actions-summary')
        @include('livewire.dashboard.partials.inspector-drawer')
    </div>
</div>
