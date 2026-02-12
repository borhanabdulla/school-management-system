@props([
    'isClean' => true,
    'missing' => 0,
    'invalid' => 0,
])

@if(! $isClean)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-l from-rose-50/90 via-rose-50/70 to-transparent p-4 dark:border-rose-700/40 dark:from-rose-900/20 dark:via-rose-900/10"
    >
        <div class="absolute right-0 top-0 h-full w-1 bg-rose-400 dark:bg-rose-600"></div>

        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-900/40">
                <svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.25-8.25-3.286Z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-bold text-rose-900 dark:text-rose-200">مشاكل تمنع الإغلاق</div>
                <div class="mt-1 flex items-center gap-3 text-xs">
                    <span class="font-semibold text-amber-700 dark:text-amber-300">Missing: <strong>{{ (int) $missing }}</strong></span>
                    <span class="h-3 w-px bg-rose-300/60 dark:bg-rose-600/40"></span>
                    <span class="font-semibold text-rose-700 dark:text-rose-300">Invalid: <strong>{{ (int) $invalid }}</strong></span>
                </div>
                <div class="mt-2 text-[11px] text-rose-700/70 dark:text-rose-400/60">راجع تقرير الصحة وأصلح المشاكل قبل المتابعة.</div>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <button
                type="button"
                wire:click="$dispatch('runGradingHealthCheckRequested')"
                class="group inline-flex items-center gap-1.5 rounded-xl border border-white bg-white px-3 py-2 text-xs font-semibold text-purple-700 shadow-sm transition hover:bg-purple-50 dark:border-slate-700 dark:bg-slate-800 dark:text-purple-300 dark:hover:bg-slate-700"
            >
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                </svg>
                فحص الصحة الآن
            </button>
        </div>
    </div>
@endif
