<div x-data="{ open: @entangle('drawerOpen') }" x-cloak>
    <div x-show="open" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/60" @click="$wire.closeDrawer()"></div>
        <div x-show="open"
            class="absolute right-0 top-0 h-full w-full sm:max-w-lg bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                            @if ($drawerType === 'health')
                                تقرير صحة النظام
                            @elseif ($drawerType === 'attendance-day')
                                تفاصيل الحضور اليومي
                            @elseif ($drawerType === 'finance')
                                مؤشرات التحصيل والفواتير
                            @elseif ($drawerType === 'enrollment-status')
                                توزيع حالة الطلاب
                            @else
                                تفاصيل المؤشر
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            @if ($drawerType === 'health')
                                {{ $drawerData['year'] ?? 'كل السنوات' }}
                            @elseif ($drawerType === 'attendance-day')
                                {{ $drawerData['date'] ?? now()->toDateString() }}
                            @elseif ($drawerType === 'finance')
                                ملخص للفترة الحالية
                            @elseif ($drawerType === 'enrollment-status')
                                {{ $drawerData['status'] ?? 'جميع الحالات' }}
                            @else
                                بيانات مخصصة حسب الفلتر
                            @endif
                        </p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        @click="$wire.closeDrawer()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    @if ($drawerType === 'health')
                        @if (!empty($drawerData['selected']))
                            <div class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 p-3 text-xs text-indigo-200">
                                عنصر مركز عليه: {{ $drawerData['selected'] }}
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">حالة الإغلاق</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ ($drawerData['can_close'] ?? false) ? 'جاهز للإغلاق' : 'غير جاهز' }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">العناصر الحرجة</p>
                                <p class="text-sm font-semibold text-red-500">
                                    {{ count($drawerData['blocking'] ?? []) }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">تنبيهات المتابعة</p>
                                <p class="text-sm font-semibold text-amber-500">
                                    {{ count($drawerData['warnings'] ?? []) }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">عناصر مانعة</h4>
                            <div class="space-y-2">
                                @forelse ($drawerData['blocking'] ?? [] as $item)
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item['label'] }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['message'] }}</p>
                                            </div>
                                            <span class="text-xs font-semibold text-red-500">{{ $item['count'] }}</span>
                                        </div>
                                        @if (!empty($item['route']))
                                            <a href="{{ route($item['route'], $item['route_params'] ?? []) }}"
                                                class="mt-2 inline-flex text-xs text-indigo-500 hover:text-indigo-400">
                                                افتح صفحة الإصلاح
                                            </a>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500 dark:text-slate-400">لا توجد عناصر مانعة حالياً.</div>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">تنبيهات</h4>
                            <div class="space-y-2">
                                @forelse ($drawerData['warnings'] ?? [] as $item)
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item['label'] }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['message'] }}</p>
                                            </div>
                                            <span class="text-xs font-semibold text-amber-500">{{ $item['count'] }}</span>
                                        </div>
                                        @if (!empty($item['route']))
                                            <a href="{{ route($item['route'], $item['route_params'] ?? []) }}"
                                                class="mt-2 inline-flex text-xs text-indigo-500 hover:text-indigo-400">
                                                افتح صفحة الإصلاح
                                            </a>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500 dark:text-slate-400">لا توجد تنبيهات حالياً.</div>
                                @endforelse
                            </div>
                        </div>
                    @elseif ($drawerType === 'attendance-day')
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($drawerData['items'] ?? [] as $item)
                                <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full {{ $dotMap[$item['color']] ?? 'bg-slate-400' }}"></span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $item['label'] }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item['total'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">أضعف الشعب بالحضور</h4>
                            <div class="space-y-2">
                                @forelse ($drawerData['classes'] ?? [] as $row)
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-slate-900 dark:text-white">{{ $row['label'] }}</p>
                                            <span class="text-xs font-semibold text-amber-500">{{ $row['rate'] }}%</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            حضور {{ $row['present'] }} من {{ $row['total'] }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500 dark:text-slate-400">لا تتوفر بيانات تفصيلية لهذا اليوم.</div>
                                @endforelse
                            </div>
                        </div>
                    @elseif ($drawerType === 'finance')
                        @if (!empty($drawerData['bucket']))
                            <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-200">
                                عرض الفواتير ضمن نطاق: {{ str_replace('aging:', '', $drawerData['bucket']) }}
                            </div>
                        @endif
                        @if (!empty($drawerData['selected_status']))
                            <div class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 p-3 text-xs text-indigo-200">
                                عرض الفواتير لحالة: {{ $drawerData['selected_status'] }}
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">إجمالي الفواتير</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ number_format($drawerData['summary']['total'] ?? 0, 0) }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">محصل</p>
                                <p class="text-sm font-semibold text-emerald-500">
                                    {{ number_format($drawerData['summary']['paid'] ?? 0, 0) }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">متبقي</p>
                                <p class="text-sm font-semibold text-amber-500">
                                    {{ number_format($drawerData['summary']['outstanding'] ?? 0, 0) }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">نسبة التحصيل</p>
                                <p class="text-sm font-semibold text-indigo-500">
                                    {{ $drawerData['summary']['paid_ratio'] ?? 0 }}%
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">عمر المتأخرات</h4>
                            <div class="grid grid-cols-2 gap-3 text-center text-xs">
                                <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                    <p class="text-slate-500 dark:text-slate-400">0-30 يوم</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $drawerData['aging']['0_30'] ?? 0 }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                    <p class="text-slate-500 dark:text-slate-400">31-60 يوم</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $drawerData['aging']['31_60'] ?? 0 }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                    <p class="text-slate-500 dark:text-slate-400">61-90 يوم</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $drawerData['aging']['61_90'] ?? 0 }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                    <p class="text-slate-500 dark:text-slate-400">+90 يوم</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $drawerData['aging']['90_plus'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">أعلى الشعب مديونية</h4>
                            <div class="space-y-2">
                                @forelse ($drawerData['top_unpaid'] ?? [] as $row)
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-slate-900 dark:text-white">{{ $row['label'] }}</p>
                                            <span class="text-xs font-semibold text-amber-500">{{ number_format($row['outstanding'], 0) }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">عدد الفواتير: {{ $row['total'] }}</p>
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500 dark:text-slate-400">لا توجد بيانات مديونية حالياً.</div>
                                @endforelse
                            </div>
                            <a href="{{ route('finance.invoices.index') }}"
                                class="mt-3 inline-flex text-xs text-indigo-500 hover:text-indigo-400">
                                عرض الفواتير التفصيلية
                            </a>
                        </div>
                    @elseif ($drawerType === 'enrollment-status')
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">الحالة</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $drawerData['status'] ?? 'جميع الحالات' }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400">إجمالي الطلاب</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $drawerData['total'] ?? 0 }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">أعلى الصفوف</h4>
                            <div class="space-y-2">
                                @forelse ($drawerData['top_grades'] ?? [] as $row)
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-slate-900 dark:text-white">{{ $row['label'] }}</p>
                                            <span class="text-xs font-semibold text-indigo-500">{{ $row['total'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500 dark:text-slate-400">لا توجد بيانات متاحة.</div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            لا توجد تفاصيل متاحة لهذا المؤشر.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
