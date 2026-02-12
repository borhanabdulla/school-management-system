@props([
    'isStale' => false,
    'lastHeartbeat' => null,
])

@if($isStale)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-l from-amber-50/90 via-amber-50/70 to-transparent p-4 dark:border-amber-700/40 dark:from-amber-900/20 dark:via-amber-900/10"
    >
        <div class="absolute right-0 top-0 h-full w-1 bg-amber-400 dark:bg-amber-600"></div>

        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-bold text-amber-900 dark:text-amber-200">مزامنة الدرجات متوقفة أو متأخرة</div>
                <div class="mt-1 text-xs text-amber-700/80 dark:text-amber-300/70">
                    آخر نبضة: <strong>{{ $lastHeartbeat ?? 'غير متوفر' }}</strong>
                </div>
                <div class="mt-1 text-[11px] text-amber-700/60 dark:text-amber-400/50">
                    إذا استمر التنبيه، شغّل الـ queue أو استخدم إعادة التجميع من تبويب المواد.
                </div>
            </div>
        </div>
    </div>
@endif
