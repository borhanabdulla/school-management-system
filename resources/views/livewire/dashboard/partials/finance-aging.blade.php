<div class="modern-card-elevated p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تقادم المتأخرات</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400">عدد الفواتير حسب عمر التأخر</p>
        </div>
        <button type="button" class="text-xs text-indigo-500 hover:text-indigo-400" wire:click="openDrawer('finance')">تفاصيل</button>
    </div>
    <div class="space-y-3">
        @foreach ($financeAging['items'] as $bucket)
            <div>
                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-slate-300 mb-1">
                    <span>{{ $bucket['label'] }}</span>
                    <span class="font-semibold">{{ $bucket['count'] }}</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2">
                    @php
                        $bucketKey = match ($bucket['label']) {
                            '0-30 يوم' => 'aging:0-30',
                            '31-60 يوم' => 'aging:31-60',
                            '61-90 يوم' => 'aging:61-90',
                            default => 'aging:90+',
                        };
                    @endphp
                    <button type="button"
                        class="bg-amber-500 h-2 rounded-full transition hover:bg-amber-400"
                        style="width: {{ ($bucket['count'] / $financeAging['max']) * 100 }}%"
                        wire:click="openDrawer('finance', '{{ $bucketKey }}')"
                        aria-label="تفاصيل {{ $bucket['label'] }}"></button>
                </div>
                <div class="text-[11px] text-gray-500 dark:text-slate-400 mt-1">
                    إجمالي متبقي: {{ number_format($bucket['amount'], 0) }}
                </div>
            </div>
        @endforeach
    </div>
</div>
