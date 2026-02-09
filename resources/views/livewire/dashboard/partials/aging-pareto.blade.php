<div class="modern-card-elevated p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">الأسباب الأكثر تأثيرًا على الجاهزية</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400">أعلى الأسباب التي تشكل معظم التعطيلات</p>
        </div>
        <button type="button" class="text-xs text-indigo-500 hover:text-indigo-400" wire:click="openDrawer('health')">تفاصيل</button>
    </div>

    <div class="space-y-3">
        @forelse ($readinessPareto['items'] as $item)
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['label'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">
                            {{ $item['count'] }} حالة — {{ $item['percent'] }}%
                        </p>
                    </div>
                    <button type="button" wire:click="openDrawer('health', '{{ $item['label'] }}')"
                        class="text-xs px-2 py-1 rounded-full bg-indigo-500/10 text-indigo-500 hover:bg-indigo-500/20">
                        تراكمي {{ $item['cumulative'] }}%
                    </button>
                </div>
                <div class="mt-2 w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($item['count'] / $readinessPareto['max']) * 100 }}%"></div>
                </div>
                @if (!empty($item['route']))
                    <a href="{{ route($item['route'], $item['route_params'] ?? []) }}"
                        class="mt-2 inline-flex text-xs text-indigo-500 hover:text-indigo-400">
                        افتح صفحة الإصلاح
                    </a>
                @endif
            </div>
        @empty
            <div class="text-sm text-gray-500 dark:text-slate-300">لا توجد أسباب جاهزية مسجّلة حالياً.</div>
        @endforelse
    </div>
</div>
