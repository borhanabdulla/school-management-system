@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $baseClasses = 'inline-flex items-center rounded-full font-medium';

    $variants = [
        'primary' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-400/20',
        'secondary' => 'bg-slate-50 text-slate-600 ring-1 ring-inset ring-slate-500/10 dark:bg-slate-800/50 dark:text-slate-300 dark:ring-slate-400/20',
        'success' => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-400/20',
        'warning' => 'bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-300 dark:ring-yellow-400/20',
        'danger' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-400/20',
        'neutral' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-800/50 dark:text-gray-300 dark:ring-gray-400/20',
    ];

    $sizes = [
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-sm px-2.5 py-0.5',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
