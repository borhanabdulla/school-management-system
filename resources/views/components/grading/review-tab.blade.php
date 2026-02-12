@props([
    'isClean' => true,
    'missing' => 0,
    'invalid' => 0,
    'terms' => [],
    'grades' => [],
    'selectedTermId' => null,
    'selectedGradeId' => null,
])

@php
    $selectedTerm = collect($terms)->firstWhere('id', $selectedTermId);
    $selectedGrade = collect($grades)->firstWhere('id', $selectedGradeId);
@endphp

<div class="space-y-6">
    {{-- Status hero --}}
    @if($isClean)
        <div class="relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50 via-emerald-50/80 to-teal-50/60 p-8 text-center dark:border-emerald-700/40 dark:from-emerald-900/30 dark:via-emerald-900/20 dark:to-teal-900/10">
            <div class="pointer-events-none absolute -left-8 -top-8 h-32 w-32 rounded-full bg-emerald-200/30 blur-2xl dark:bg-emerald-700/20"></div>
            <div class="pointer-events-none absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-teal-300/20 blur-xl dark:bg-teal-700/15"></div>

            <div class="relative">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 shadow-lg shadow-emerald-500/10 dark:bg-emerald-800/40">
                    <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-xl font-bold text-emerald-800 dark:text-emerald-200">كل شيء جاهز! 🎉</h3>
                <p class="mt-2 text-sm text-emerald-700/80 dark:text-emerald-300/70">
                    لا توجد مشاكل: يمكنك المتابعة للمعالجة والنشر والإغلاق.
                </p>
            </div>
        </div>
    @else
        <div class="relative overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-br from-rose-50 via-rose-50/80 to-amber-50/60 p-8 text-center dark:border-rose-700/40 dark:from-rose-900/30 dark:via-rose-900/20 dark:to-amber-900/10">
            <div class="pointer-events-none absolute -left-8 -top-8 h-32 w-32 rounded-full bg-rose-200/30 blur-2xl dark:bg-rose-700/20"></div>

            <div class="relative">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-100 shadow-lg shadow-rose-500/10 dark:bg-rose-800/40">
                    <svg class="h-8 w-8 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-xl font-bold text-rose-800 dark:text-rose-200">بحاجة إصلاح</h3>
                <p class="mt-2 text-sm text-rose-700/80 dark:text-rose-300/70">
                    توجد مشاكل تمنع العمليات الحساسة (المعالجة/النشر/الإغلاق)
                </p>

                <div class="mt-5 flex items-center justify-center gap-4">
                    <div class="rounded-2xl border border-amber-200/60 bg-amber-50/80 px-5 py-3 dark:border-amber-700/40 dark:bg-amber-900/20">
                        <div class="text-2xl font-black text-amber-600">{{ (int) $missing }}</div>
                        <div class="text-[11px] font-semibold text-amber-700/70 dark:text-amber-300/70">Missing</div>
                    </div>
                    <div class="rounded-2xl border border-rose-200/60 bg-rose-50/80 px-5 py-3 dark:border-rose-700/40 dark:bg-rose-900/20">
                        <div class="text-2xl font-black text-rose-600">{{ (int) $invalid }}</div>
                        <div class="text-[11px] font-semibold text-rose-700/70 dark:text-rose-300/70">Invalid</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Checklist --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm dark:border-slate-700/50 dark:bg-slate-900/80">
        <div class="mb-4 flex items-center gap-2">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-6.548 0c-1.131.094-1.976 1.057-1.976 2.192V16.5A2.25 2.25 0 0 0 12 18.75h.75" />
            </svg>
            <span class="text-sm font-bold text-gray-800 dark:text-white">قائمة المراجعة</span>
        </div>

        <div class="space-y-2">
            @php
                $checks = [
                    ['label' => 'قوالب الدرجات معرّفة', 'pass' => true],
                    ['label' => 'جميع المواد مربوطة بقالب', 'pass' => (int) $missing === 0],
                    ['label' => 'أوزان جميع القوالب = 100%', 'pass' => (int) $invalid === 0],
                    ['label' => 'بنود الدفتر الشهري مربوطة', 'pass' => (int) $missing === 0],
                    ['label' => 'أوزان الفصول = 100%', 'pass' => $isClean],
                    ['label' => 'سلم التقديرات محدد', 'pass' => true],
                ];
            @endphp
            @foreach($checks as $check)
                <div class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 {{ $check['pass'] ? 'bg-emerald-50/60 dark:bg-emerald-900/10' : 'bg-rose-50/60 dark:bg-rose-900/10' }}">
                    @if($check['pass'])
                        <svg class="h-5 w-5 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @else
                        <svg class="h-5 w-5 flex-shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @endif
                    <span class="text-sm {{ $check['pass'] ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300' }}">{{ $check['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Action --}}
    <div class="flex justify-center">
        <button
            type="button"
            wire:click="$dispatch('runGradingHealthCheckRequested')"
            class="group inline-flex items-center gap-2 rounded-xl border border-purple-200/60 bg-purple-50/80 px-6 py-3 text-sm font-semibold text-purple-700 transition-all hover:bg-purple-100 hover:shadow-md dark:border-purple-700/40 dark:bg-purple-900/20 dark:text-purple-300 dark:hover:bg-purple-900/40"
        >
            <svg class="h-5 w-5 transition-transform duration-300 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
            </svg>
            تشغيل فحص الصحة الآن
        </button>
    </div>
</div>
