<div class="rounded-2xl border border-gray-200/60 bg-white/95 p-6 shadow-sm backdrop-blur lg:sticky lg:top-6 dark:border-slate-700/60 dark:bg-slate-900/80">
    @php
        $checked = $report['checked'] ?? 0;
        $missingCount = count($report['missing']);
        $invalidCount = count($report['invalid']);
        // $warningCount = count($report['warnings']); // Not used in score but shown?

        // Calculate scores
        $linkage = $checked > 0 ? round((($checked - $missingCount) / $checked) * 100) : ($missingCount > 0 ? 0 : 100);
        $integrity = max(0, 100 - ($invalidCount * 5));
        $readiness = ($missingCount == 0 && $invalidCount == 0) ? 100 : round(($linkage * 0.4) + ($integrity * 0.6));

        $issues = $missingCount + $invalidCount;
        $issuesColor = $issues > 0 ? '#f43f5e' : '#10b981';
        $issuesPercent = min(100, $issues * 5);
    @endphp

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">تشخيص الصحة</h3>
        <div class="flex items-center gap-2">
            <span wire:loading wire:target="refreshReport" class="flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-purple-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-purple-500"></span>
            </span>
            <button
                type="button"
                wire:click="refreshReport"
                wire:loading.class="animate-spin opacity-50"
                class="text-gray-400 hover:text-purple-600 transition-colors dark:hover:text-purple-400"
                title="تحديث الفحص"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                </svg>
            </button>
        </div>
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

        <!-- Missing Items -->
        @if($missingCount > 0)
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-amber-700 dark:text-amber-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    نقص في الربط ({{ $missingCount }})
                </div>
                <div class="max-h-60 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                    @foreach($report['missing'] as $row)
                        <div class="relative overflow-hidden rounded-lg border-l-4 border-amber-500 bg-gray-50 p-3 shadow-sm dark:bg-slate-800/80">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400">MISSING LINK</div>
                                    <div class="mt-1 text-xs font-medium text-gray-800 dark:text-gray-200">
                                        المادة غير مربوطة بأي قالب درجات.
                                    </div>
                                </div>
                                <div class="text-[10px] font-mono text-gray-400">
                                    Subject #{{ $row['subject_id'] ?? '?' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Invalid Items -->
        @if($invalidCount > 0)
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-rose-700 dark:text-rose-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    أخطاء حرجة ({{ $invalidCount }})
                </div>
                <div class="max-h-60 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                    @foreach($report['invalid'] as $row)
                        <div class="relative overflow-hidden rounded-lg border-l-4 border-rose-500 bg-gray-50 p-3 shadow-sm dark:bg-slate-800/80">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase">
                                        {{ $row['violation']['severity'] ?? 'INVALID' }}
                                    </div>
                                    <div class="mt-1 text-xs font-medium text-gray-800 dark:text-gray-200">
                                        {{ $row['violation']['message'] ?? 'خطأ غير معرف' }}
                                    </div>
                                </div>
                                <div class="text-[10px] font-mono text-gray-400">
                                    Subject #{{ $row['subject_id'] ?? '?' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($missingCount === 0 && $invalidCount === 0)
            <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3 dark:bg-emerald-900/20">
                <div class="text-emerald-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                    النظام يعمل بكفاءة عالية.
                </div>
            </div>
        @endif
    </div>
</div>
