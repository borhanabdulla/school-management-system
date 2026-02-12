@props([
    'steps' => [],
    'active' => null,
    'orientation' => 'vertical',
    'interactive' => true,
])

@php
    $activeIndex = collect($steps)->search(fn ($step) => ($step['key'] ?? null) === $active);
    $activeIndex = $activeIndex === false ? 0 : $activeIndex;

    $stepIcons = [
        'templates' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
        'subjects'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />',
        'monthly'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
        'general'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
        'scale'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
        'review'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />',
    ];

    $preparedSteps = [];
    foreach ($steps as $index => $step) {
        $key = $step['key'] ?? $index;
        $isActive = $index === $activeIndex;
        $isComplete = $index < $activeIndex;
        $state = $step['state'] ?? null;

        $preparedSteps[] = [
            'key'        => $key,
            'label'      => $step['label'] ?? ($key ?? 'خطوة'),
            'hint'       => $step['hint'] ?? null,
            'isActive'   => $isActive,
            'isComplete' => $isComplete,
            'state'      => $state,
            'icon'       => $stepIcons[$key] ?? $stepIcons['general'],
            'number'     => $index + 1,
        ];
    }
    $totalSteps = count($preparedSteps);
    $isHorizontal = $orientation === 'horizontal';
@endphp

<div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/80">
    @if($isHorizontal)
        {{-- ═══════════════════════════════════════════
             HORIZONTAL STEPPER (Timeline Style)
        ═══════════════════════════════════════════ --}}
        <div class="relative">
            {{-- Background connecting line --}}
            <div class="absolute top-6 right-6 left-6 h-0.5 bg-gray-200 dark:bg-slate-700"></div>
            {{-- Progress line --}}
            @if($activeIndex > 0)
                <div
                    class="absolute top-6 right-6 h-0.5 bg-gradient-to-l from-purple-500 to-emerald-500 transition-all duration-700 ease-out"
                    style="width: {{ ($activeIndex / max(1, $totalSteps - 1)) * 100 }}%;"
                ></div>
            @endif

            <div class="relative flex items-start justify-between">
                @foreach($preparedSteps as $step)
                    @php
                        $ringClass = $step['isActive']
                            ? 'ring-[3px] ring-purple-400/40 dark:ring-purple-500/30 bg-gradient-to-br from-purple-500 to-purple-700 text-white shadow-lg shadow-purple-500/25 scale-110'
                            : ($step['isComplete']
                                ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20'
                                : ($step['state'] === 'warning'
                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 ring-2 ring-amber-300/50'
                                    : 'bg-gray-100 text-gray-500 dark:bg-slate-800 dark:text-slate-400'));
                        $labelClass = $step['isActive']
                            ? 'text-purple-700 dark:text-purple-300 font-bold'
                            : ($step['isComplete']
                                ? 'text-emerald-700 dark:text-emerald-400 font-semibold'
                                : 'text-gray-500 dark:text-slate-400 font-medium');
                    @endphp

                    <div class="flex flex-col items-center gap-2 {{ $interactive ? 'cursor-pointer' : '' }}"
                         style="width: {{ 100 / $totalSteps }}%"
                         @if($interactive) wire:click="setTab('{{ $step['key'] }}')" @endif>
                        {{-- Step circle with icon --}}
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-full transition-all duration-500 {{ $ringClass }}">
                            @if($step['isComplete'])
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @elseif($step['state'] === 'warning')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    {!! $step['icon'] !!}
                                </svg>
                            @endif

                            {{-- Pulse effect for active step --}}
                            @if($step['isActive'])
                                <span class="absolute inset-0 animate-ping rounded-full bg-purple-400/20"></span>
                            @endif
                        </div>

                        {{-- Step label --}}
                        <div class="text-center">
                            <div class="text-[11px] {{ $labelClass }} transition-colors duration-300">
                                {{ $step['label'] }}
                            </div>
                            @if($step['hint'] && $step['isActive'])
                                <div class="mt-0.5 text-[10px] text-gray-400 dark:text-slate-500 max-w-[100px] leading-tight">
                                    {{ $step['hint'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- ═══════════════════════════════════════════
             VERTICAL STEPPER (Sidebar Style)
        ═══════════════════════════════════════════ --}}
        <div class="mb-3 flex items-center gap-2">
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/40">
                <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-400 dark:text-slate-500">خريطة الإعداد</div>
                <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $activeIndex + 1 }} من {{ $totalSteps }}</div>
            </div>
        </div>

        <div class="relative space-y-1">
            {{-- Vertical connecting line --}}
            <div class="absolute right-[23px] top-4 bottom-4 w-0.5 bg-gray-100 dark:bg-slate-800"></div>

            @foreach($preparedSteps as $step)
                @php
                    $circleClass = $step['isActive']
                        ? 'bg-gradient-to-br from-purple-500 to-purple-700 text-white ring-[3px] ring-purple-400/30 shadow-md shadow-purple-500/20'
                        : ($step['isComplete']
                            ? 'bg-emerald-500 text-white'
                            : ($step['state'] === 'warning'
                                ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400'
                                : 'bg-gray-100 text-gray-400 dark:bg-slate-800 dark:text-slate-500'));
                    $rowClass = $step['isActive']
                        ? 'bg-purple-50/80 border-purple-200/60 dark:bg-purple-900/20 dark:border-purple-700/40'
                        : 'border-transparent hover:bg-gray-50/80 dark:hover:bg-slate-800/40';
                @endphp

                <div class="relative">
                    @if($interactive)
                        <button
                            type="button"
                            wire:click="setTab('{{ $step['key'] }}')"
                            class="flex w-full items-center gap-3 rounded-xl border px-3 py-3 text-right transition-all duration-300 {{ $rowClass }}"
                        >
                    @else
                        <div class="flex w-full items-center gap-3 rounded-xl border px-3 py-3 text-right transition-all duration-300 {{ $rowClass }}">
                    @endif
                        {{-- Circle --}}
                        <div class="relative z-10 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full transition-all duration-500 {{ $circleClass }}">
                            @if($step['isComplete'])
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    {!! $step['icon'] !!}
                                </svg>
                            @endif
                        </div>

                        {{-- Label --}}
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold {{ $step['isActive'] ? 'text-purple-700 dark:text-purple-300' : ($step['isComplete'] ? 'text-gray-700 dark:text-gray-200' : 'text-gray-600 dark:text-gray-300') }}">
                                {{ $step['label'] }}
                            </div>
                            @if(! empty($step['hint']))
                                <div class="mt-0.5 text-[11px] text-gray-400 dark:text-slate-500 leading-tight">{{ $step['hint'] }}</div>
                            @endif
                        </div>

                        {{-- Step number --}}
                        <span class="text-[10px] font-bold {{ $step['isActive'] ? 'text-purple-400' : 'text-gray-300 dark:text-slate-600' }}">
                            {{ $step['number'] }}
                        </span>
                    @if($interactive)
                        </button>
                    @else
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
