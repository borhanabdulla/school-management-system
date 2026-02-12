@props([
    'title' => '',
    'message' => '',
    'variant' => 'info',
    'dismissible' => true,
])

@php
    $config = match($variant) {
        'warning' => [
            'border' => 'border-amber-300/50 dark:border-amber-500/30',
            'bg' => 'bg-gradient-to-l from-amber-50/90 via-amber-50/70 to-amber-100/50 dark:from-amber-950/40 dark:via-amber-950/20 dark:to-amber-900/30',
            'icon_bg' => 'bg-amber-100 dark:bg-amber-900/50',
            'icon_color' => 'text-amber-600 dark:text-amber-400',
            'title_color' => 'text-amber-900 dark:text-amber-200',
            'text_color' => 'text-amber-800/90 dark:text-amber-300/90',
            'glow' => 'shadow-amber-200/30 dark:shadow-amber-800/20',
        ],
        'danger' => [
            'border' => 'border-rose-300/50 dark:border-rose-500/30',
            'bg' => 'bg-gradient-to-l from-rose-50/90 via-rose-50/70 to-rose-100/50 dark:from-rose-950/40 dark:via-rose-950/20 dark:to-rose-900/30',
            'icon_bg' => 'bg-rose-100 dark:bg-rose-900/50',
            'icon_color' => 'text-rose-600 dark:text-rose-400',
            'title_color' => 'text-rose-900 dark:text-rose-200',
            'text_color' => 'text-rose-800/90 dark:text-rose-300/90',
            'glow' => 'shadow-rose-200/30 dark:shadow-rose-800/20',
        ],
        default => [
            'border' => 'border-indigo-300/50 dark:border-indigo-500/30',
            'bg' => 'bg-gradient-to-l from-indigo-50/90 via-indigo-50/70 to-blue-100/50 dark:from-indigo-950/40 dark:via-indigo-950/20 dark:to-indigo-900/30',
            'icon_bg' => 'bg-indigo-100 dark:bg-indigo-900/50',
            'icon_color' => 'text-indigo-600 dark:text-indigo-400',
            'title_color' => 'text-indigo-900 dark:text-indigo-200',
            'text_color' => 'text-indigo-800/90 dark:text-indigo-300/90',
            'glow' => 'shadow-indigo-200/30 dark:shadow-indigo-800/20',
        ],
    };
@endphp

<div
    x-data="{ visible: true }"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="group relative overflow-hidden rounded-2xl border px-5 py-4 shadow-sm backdrop-blur-sm {{ $config['border'] }} {{ $config['bg'] }} {{ $config['glow'] }}"
>
    {{-- Decorative gradient accent line --}}
    <div class="absolute right-0 top-0 h-full w-1 rounded-r-2xl {{ $config['icon_bg'] }}"></div>

    <div class="flex items-start gap-3.5">
        {{-- Icon --}}
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl {{ $config['icon_bg'] }} {{ $config['icon_color'] }}">
            @if($variant === 'warning')
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            @elseif($variant === 'danger')
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.25-8.25-3.286Z" />
                </svg>
            @else
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                </svg>
            @endif
        </div>

        {{-- Content --}}
        <div class="min-w-0 flex-1">
            @if($title)
                <div class="text-sm font-bold {{ $config['title_color'] }}">{{ $title }}</div>
            @endif
            <div class="mt-0.5 text-[13px] leading-relaxed {{ $config['text_color'] }}">{{ $message }}</div>
        </div>

        {{-- Dismiss button --}}
        @if($dismissible)
            <button
                @click="visible = false"
                class="flex-shrink-0 rounded-lg p-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100 {{ $config['icon_color'] }} hover:{{ $config['icon_bg'] }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>
