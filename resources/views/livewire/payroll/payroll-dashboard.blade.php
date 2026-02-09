<div>
    <div class="space-y-8">
        {{-- Header Section --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-8 text-white shadow-xl">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-64 w-64 rounded-full bg-teal-500/20 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row justify-between items-end gap-4">
                <div>
                    <h2 class="text-4xl font-bold font-display tracking-tight">اللوحة المالية</h2>
                    <p class="mt-2 text-emerald-100 text-lg opacity-90">نظرة شاملة على الرواتب، الدفعات، والتكاليف.</p>
                </div>
                <div class="flex gap-3">
                    <span class="px-4 py-2 bg-white/10 rounded-lg backdrop-blur-sm border border-white/10 text-sm font-medium">
                        {{ now()->translatedFormat('F Y') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Cost -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-medium {{ $stats['cost_trend'] >= 0 ? 'text-red-600 bg-red-50 dark:bg-red-900/20' : 'text-green-600 bg-green-50 dark:bg-green-900/20' }} px-2 py-1 rounded-lg flex items-center gap-1">
                            <svg class="w-3 h-3 {{ $stats['cost_trend'] < 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            {{ abs($stats['cost_trend']) }}%
                        </span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($stats['total_cost']) }} <span class="text-sm font-normal text-gray-500">ريال</span></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">إجمالي الرواتب (هذا الشهر)</p>
                </div>
            </div>

            <!-- Active Staff -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['active_staff'] }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">موظف بعقد نشط</p>
                </div>
            </div>

            <!-- Pending Batches -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 dark:bg-orange-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['pending_batches'] }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">دفعات معلقة</p>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Monthly Trend -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">تطور الرواتب (6 أشهر)</h3>
                <div id="payrollTrendChart" class="w-full h-80"></div>
            </div>

            <!-- Cost Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">توزيع التكلفة</h3>
                <div id="costDistributionChart" class="w-full h-80 flex items-center justify-center"></div>
            </div>
        </div>

        {{-- Bottom Section: Quick Actions & Recent Activity --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-2xl">⚡</span>
                    إجراءات سريعة
                </h3>
                <div class="grid grid-cols-1 gap-4">
                    <a href="{{ route('payroll.batches.index') }}" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/30 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 border border-gray-100 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-emerald-800 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white">إنشاء دفعة جديدة</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">بدء دورة رواتب لشهر جديد</p>
                        </div>
                    </a>

                    <a href="{{ route('payroll.contracts.index') }}" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/30 hover:bg-blue-50 dark:hover:bg-blue-900/20 border border-gray-100 dark:border-gray-700 hover:border-blue-200 dark:hover:border-blue-800 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white">إضافة عقد</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">تسجيل عقد جديد لموظف</p>
                        </div>
                    </a>

                    <a href="{{ route('payroll.salary-components.index') }}" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/30 hover:bg-purple-50 dark:hover:bg-purple-900/20 border border-gray-100 dark:border-gray-700 hover:border-purple-200 dark:hover:border-purple-800 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white">إدارة البنود</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">تعديل البدلات والخصومات</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">آخر النشاطات</h3>
                    <a href="{{ route('payroll.batches.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 hover:underline">عرض الكل</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 rounded-r-lg">الدفعة</th>
                                <th class="px-4 py-3">الفترة</th>
                                <th class="px-4 py-3">الحالة</th>
                                <th class="px-4 py-3">بواسطة</th>
                                <th class="px-4 py-3 rounded-l-lg">التاريخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($recentBatches as $batch)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $batch->name }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $batch->period_label }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $batch->status_color }}-100 text-{{ $batch->status_color }}-800 dark:bg-{{ $batch->status_color }}-900/30 dark:text-{{ $batch->status_color }}-300">
                                            {{ $batch->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $batch->generatedByUser?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $batch->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">لا توجد نشاطات حديثة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ApexCharts Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:navigated', function () {
            initCharts();
        });

        function initCharts() {
            // Monthly Trend Chart
            const trendData = @json($monthlyTrend);
            const trendOptions = {
                series: [{
                    name: 'الراتب الإجمالي',
                    data: trendData.map(item => item.gross)
                }, {
                    name: 'الصافي',
                    data: trendData.map(item => item.net)
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                colors: ['#10b981', '#3b82f6'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: trendData.map(item => item.month),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return value.toLocaleString() + " ر.س";
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3,
                        stops: [0, 90, 100]
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toLocaleString() + " ر.س"
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                }
            };
            const trendChart = new ApexCharts(document.querySelector("#payrollTrendChart"), trendOptions);
            trendChart.render();

            // Cost Distribution Chart
            const costData = @json($costDistribution);
            const distributionOptions = {
                series: [parseFloat(costData.basic), parseFloat(costData.allowances), parseFloat(costData.deductions)],
                labels: ['الراتب الأساسي', 'البدلات', 'الخصومات'],
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'inherit'
                },
                colors: ['#3b82f6', '#10b981', '#ef4444'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'الإجمالي',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val.toLocaleString() + " ر.س"
                        }
                    }
                }
            };
            const distributionChart = new ApexCharts(document.querySelector("#costDistributionChart"), distributionOptions);
            distributionChart.render();
        }
    </script>
</div>
