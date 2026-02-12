@props([
    'termId' => null,
    'readiness' => 100,
    'integrity' => 100,
    'linkage' => 100,
    'missingCount' => 0,
    'invalidCount' => 0,
])

<div class="rounded-2xl border border-gray-200/60 bg-white/95 p-6 shadow-sm backdrop-blur lg:sticky lg:top-6 dark:border-slate-700/60 dark:bg-slate-900/80">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">تشخيص الصحة</h3>
        <button
            type="button"
            wire:click="$dispatch('runGradingHealthCheckRequested')"
            class="text-gray-400 hover:text-purple-600 transition-colors dark:hover:text-purple-400"
            title="تحديث الفحص"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
            </svg>
        </button>
    </div>

    {{-- Main Gauge (Readiness) --}}
    <div class="flex justify-center mb-8">
        <div class="relative h-40 w-40">
            {{-- Outer Glow --}}
            <div class="absolute inset-0 rounded-full bg-emerald-400/20 blur-xl dark:bg-emerald-500/10"></div>
            
            {{-- Gauge Ring --}}
            <div class="h-40 w-40 rounded-full"
                 style="background: conic-gradient(from 0deg, #10b981 {{ $readiness }}%, #f3f4f6 {{ $readiness }}% 100%); mask-image: radial-gradient(transparent 65%, black 66%); -webkit-mask-image: radial-gradient(transparent 65%, black 66%); transform: rotate(-90deg);">
            </div>
            
            {{-- Inner Content --}}
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                @if($readiness == 100)
                    <div class="mb-1 rounded-full bg-emerald-100 p-1.5 dark:bg-emerald-900/40">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                @endif
                <span class="text-3xl font-black text-gray-800 dark:text-white">{{ $readiness }}%</span>
                <span class="text-[11px] font-medium text-gray-500 dark:text-slate-400">جاهزية النظام</span>
            </div>
        </div>
    </div>

    {{-- Secondary Stats --}}
    <div class="grid grid-cols-3 gap-2 mb-8">
        {{-- Integrity --}}
        <div class="flex flex-col items-center gap-2">
            <div class="relative h-14 w-14">
                <div class="h-14 w-14 rounded-full"
                     style="background: conic-gradient(from 0deg, #8b5cf6 {{ $integrity }}%, #f3f4f6 {{ $integrity }}% 100%); mask-image: radial-gradient(transparent 60%, black 61%); -webkit-mask-image: radial-gradient(transparent 60%, black 61%); transform: rotate(-90deg);">
                </div>
                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-700 dark:text-gray-200">
                    {{ $integrity }}%
                </div>
            </div>
            <span class="text-[10px] text-gray-500 dark:text-slate-400">سلامة البيانات</span>
        </div>

        {{-- Linkage --}}
        <div class="flex flex-col items-center gap-2">
            <div class="relative h-14 w-14">
                <div class="h-14 w-14 rounded-full"
                     style="background: conic-gradient(from 0deg, #f59e0b {{ $linkage }}%, #f3f4f6 {{ $linkage }}% 100%); mask-image: radial-gradient(transparent 60%, black 61%); -webkit-mask-image: radial-gradient(transparent 60%, black 61%); transform: rotate(-90deg);">
                </div>
                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-700 dark:text-gray-200">
                    {{ $linkage }}%
                </div>
            </div>
            <span class="text-[10px] text-gray-500 dark:text-slate-400">اكتمال الربط</span>
        </div>

        {{-- Issues (Inverse) --}}
        @php
            $issues = $missingCount + $invalidCount;
            $issuesColor = $issues > 0 ? '#f43f5e' : '#10b981';
            $issuesPercent = min(100, $issues * 5); // Just for visualization
        @endphp
        <div class="flex flex-col items-center gap-2">
            <div class="relative h-14 w-14">
                <div class="h-14 w-14 rounded-full"
                     style="background: conic-gradient(from 0deg, {{ $issuesColor }} {{ $issuesPercent }}%, #f3f4f6 {{ $issuesPercent }}% 100%); mask-image: radial-gradient(transparent 60%, black 61%); -webkit-mask-image: radial-gradient(transparent 60%, black 61%); transform: rotate(-90deg);">
                </div>
                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold {{ $issues > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $issues }}
                </div>
            </div>
            <span class="text-[10px] text-gray-500 dark:text-slate-400">أخطاء</span>
        </div>
    </div>

    {{-- System Alerts List --}}
    <div class="space-y-3">
        <h4 class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            تنبيهات النظام
        </h4>

        @if($missingCount > 0)
            <div class="flex items-start gap-3 rounded-xl bg-amber-50 p-2.5 dark:bg-amber-900/20">
                <div class="mt-0.5 text-amber-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-amber-700 dark:text-amber-300">نقص في الربط</div>
                    <div class="text-[10px] text-amber-600/80 dark:text-amber-400/70">يوجد {{ $missingCount }} مواد غير مربوطة بقالب.</div>
                </div>
            </div>
        @endif

        @if($invalidCount > 0)
            <div class="flex items-start gap-3 rounded-xl bg-rose-50 p-2.5 dark:bg-rose-900/20">
                <div class="mt-0.5 text-rose-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.25-8.25-3.286Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-rose-700 dark:text-rose-300">أخطاء في البيانات</div>
                    <div class="text-[10px] text-rose-600/80 dark:text-rose-400/70">يوجد {{ $invalidCount }} قوالب بها أخطاء أوزان.</div>
                </div>
            </div>
        @endif

        @if($missingCount === 0 && $invalidCount === 0)
            <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-2.5 dark:bg-emerald-900/20">
                <div class="text-emerald-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                    النظام يعمل بكفاءة عالية.
                </div>
            </div>
        @endif
        
        <div class="pt-2">
            <div class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] text-gray-500 dark:text-slate-400">تيارع البيانات</span>
                </div>
                <span class="text-[10px] font-mono text-gray-400">Running</span>
            </div>
        </div>
    </div>
</div>
