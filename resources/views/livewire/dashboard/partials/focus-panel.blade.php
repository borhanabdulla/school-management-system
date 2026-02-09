<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <div class="modern-card-elevated p-6 lg:col-span-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">لوحة القياس التفاعلية</h3>
                <p class="text-sm text-gray-500 dark:text-slate-300">بدّل بين مؤشرات الحضور، المالية، وحالة الطلاب.</p>
            </div>
            <div class="inline-flex rounded-full bg-slate-100 dark:bg-slate-800/70 p-1">
                @foreach ($focusTabs as $key => $label)
                    <button wire:click="setFocus('{{ $key }}')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-full transition {{ $focus === $key ? 'bg-indigo-500 text-white' : 'text-slate-600 dark:text-slate-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            @if ($focus === 'attendance')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="relative rounded-2xl border border-gray-200 dark:border-slate-700 p-4 bg-white/80 dark:bg-slate-900/60">
                            <div class="h-44">
                                <livewire:livewire-area-chart :area-chart-model="$attendanceFocusChart"
                                    key="{{ $attendanceFocusChart->reactiveKey() }}" />
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-slate-300">
                                <div class="rounded-xl bg-slate-100 dark:bg-slate-800/70 p-3">
                                    <p class="text-xs text-gray-500 dark:text-slate-400">متوسط الحضور</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ collect($attendanceTrend['items'])->avg('rate') ? round(collect($attendanceTrend['items'])->avg('rate')) : 0 }}%
                                    </p>
                                </div>
                                <div class="rounded-xl bg-slate-100 dark:bg-slate-800/70 p-3">
                                    <p class="text-xs text-gray-500 dark:text-slate-400">أعلى يوم</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ collect($attendanceTrend['items'])->max('rate') }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">تفصيل اليوم</p>
                        @foreach ($attendanceBreakdown['items'] as $item)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-slate-700 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $dotMap[$item['color']] ?? 'bg-slate-400' }}"></span>
                                    <span class="text-xs text-gray-600 dark:text-slate-300">{{ $item['label'] }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['total'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($focus === 'finance')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col items-center justify-center gap-4">
                        <div class="h-44 w-full">
                            <livewire:livewire-pie-chart :pie-chart-model="$invoiceBreakdownChart"
                                key="{{ $invoiceBreakdownChart->reactiveKey() }}" />
                        </div>
                        <div class="rounded-xl bg-slate-100 dark:bg-slate-800/70 px-4 py-3 text-center">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $finance['paid_ratio'] }}%</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400">نسبة التحصيل</div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">حالة الفواتير</p>
                        @foreach ($invoiceBreakdown['items'] as $item)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-slate-700 px-3 py-2">
                                <span class="text-xs text-gray-600 dark:text-slate-300">{{ $item['label'] }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['total'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="h-44">
                        <livewire:livewire-column-chart :column-chart-model="$enrollmentBreakdownChart"
                            key="{{ $enrollmentBreakdownChart->reactiveKey() }}" />
                    </div>
                    <div class="space-y-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">توزيع حالات الطلاب</p>
                        @foreach ($enrollmentBreakdown['items'] as $item)
                            <div>
                                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-slate-300 mb-1">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="font-semibold">{{ $item['total'] }}</span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2">
                                    <div class="{{ $barMap[$item['color']] ?? 'bg-indigo-500' }} h-2 rounded-full" style="width: {{ ($item['total'] / $enrollmentBreakdown['max']) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modern-card-elevated p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">جاهزية الإغلاق</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-slate-300">مشاكل مانعة</span>
                <span class="text-sm font-semibold text-red-500">{{ $stats['readiness_blocking'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-slate-300">تنبيهات</span>
                <span class="text-sm font-semibold text-amber-500">{{ $stats['readiness_warnings'] }}</span>
            </div>
            <div class="border-t border-gray-200 dark:border-slate-700 pt-4">
                <div class="text-xs text-gray-500 dark:text-slate-400 mb-2">حالة الفصول</div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-slate-300">نشطة</span>
                    <span class="font-semibold text-indigo-500">{{ $termsSummary['active'] }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-slate-300">مكتملة</span>
                    <span class="font-semibold text-emerald-500">{{ $termsSummary['completed'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
