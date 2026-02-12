@props([
    'show' => false,
    'step' => null,
])

@php
    $stepIconMap = [
        'templates' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
        'subjects'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />',
        'monthly'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
        'general'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
        'scale'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
        'review'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />',
    ];

    $stepsOrder = ['templates', 'subjects', 'monthly', 'general', 'scale', 'review'];
    $stepKey = null;
    if ($step) {
        foreach ($stepsOrder as $k) {
            $label = $step['label'] ?? '';
            if (isset($stepIconMap[$k])) {
                $stepKey = $k;
            }
        }
    }
    $currentIndex = $stepKey ? array_search($stepKey, $stepsOrder) : 0;
    $totalSteps = count($stepsOrder);
    $stepIcon = $stepIconMap[$stepKey ?? 'general'] ?? $stepIconMap['general'];
@endphp

@if($show && $step)
    <div
        x-data="{ open: true }"
        x-show="open"
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="dismissStepHelp" @click="open = false"></div>

        {{-- Modal --}}
        <div
            x-transition:enter="transition ease-out duration-400 delay-100"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl dark:border-slate-700/60 dark:bg-slate-900"
        >
            {{-- Gradient header background --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 px-6 pb-12 pt-6">
                {{-- Decorative circles --}}
                <div class="pointer-events-none absolute -left-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-purple-400/20 blur-xl"></div>

                {{-- Close button --}}
                <button
                    wire:click="dismissStepHelp"
                    @click="open = false"
                    class="absolute left-4 top-4 rounded-lg p-1.5 text-white/60 transition hover:bg-white/10 hover:text-white"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Step badge --}}
                <div class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold text-white/90 backdrop-blur-sm">
                    <span>{{ $step['label'] ?? '' }}</span>
                </div>

                {{-- Icon --}}
                <div class="mt-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 shadow-lg backdrop-blur-sm">
                    <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        {!! $stepIcon !!}
                    </svg>
                </div>

                <h3 class="mt-3 text-xl font-bold text-white">{{ $step['title'] ?? '' }}</h3>
            </div>

            {{-- Content --}}
            <div class="-mt-6 rounded-t-3xl bg-white px-6 pb-6 pt-6 dark:bg-slate-900">
                <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ $step['description'] ?? '' }}
                </p>

                @if(!empty($step['effects']))
                    <div class="mt-5 space-y-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                            أثر هذه الخطوة
                        </div>
                        @foreach($step['effects'] as $effect)
                            <div class="flex items-start gap-2.5 rounded-xl bg-gray-50 px-3.5 py-2.5 dark:bg-slate-800/60">
                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                                <span class="text-[13px] text-gray-700 dark:text-gray-300">{{ $effect }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Action button --}}
                <div class="mt-6 flex justify-end">
                    <button
                        wire:click="dismissStepHelp"
                        @click="open = false"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-purple-600 to-purple-700 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-purple-500/20 transition-all hover:shadow-xl hover:shadow-purple-500/30 hover:-translate-y-0.5"
                    >
                        <span>ابدأ الخطوة</span>
                        <svg class="h-4 w-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
