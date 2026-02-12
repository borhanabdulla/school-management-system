@props([
    'label' => '',
    'value' => 0,
    'tone' => 'default',
])

@php
    $config = match($tone) {
        'danger' => [
            'text' => 'text-rose-600 dark:text-rose-400',
            'bg' => 'bg-rose-50/60 border-rose-200/50 dark:bg-rose-900/15 dark:border-rose-700/30',
            'icon_bg' => 'bg-rose-100 dark:bg-rose-900/40',
        ],
        'warning' => [
            'text' => 'text-amber-600 dark:text-amber-400',
            'bg' => 'bg-amber-50/60 border-amber-200/50 dark:bg-amber-900/15 dark:border-amber-700/30',
            'icon_bg' => 'bg-amber-100 dark:bg-amber-900/40',
        ],
        'info' => [
            'text' => 'text-indigo-600 dark:text-indigo-400',
            'bg' => 'bg-indigo-50/60 border-indigo-200/50 dark:bg-indigo-900/15 dark:border-indigo-700/30',
            'icon_bg' => 'bg-indigo-100 dark:bg-indigo-900/40',
        ],
        default => [
            'text' => 'text-gray-900 dark:text-white',
            'bg' => 'bg-gray-50/60 border-gray-200/50 dark:bg-slate-800/40 dark:border-slate-700/30',
            'icon_bg' => 'bg-gray-100 dark:bg-slate-800',
        ],
    };
@endphp

<div class="rounded-xl border p-3 {{ $config['bg'] }}">
    <div class="text-[11px] font-medium text-gray-500 dark:text-slate-400">{{ $label }}</div>
    <div class="mt-1 text-2xl font-black {{ $config['text'] }}">{{ $value }}</div>
</div>
