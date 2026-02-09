@props(['label', 'value', 'icon', 'color' => 'blue'])

@php
    $colorClasses = [
        'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
        'green' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
        'gray' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
    ];
    $iconColor = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="bg-surface p-4 rounded-xl border border-border shadow-sm flex items-center gap-4">
    <div class="w-12 h-12 rounded-full {{ $iconColor }} flex items-center justify-center">
        {{ $icon }}
    </div>
    <div>
        <div class="text-sm text-secondary font-medium">{{ $label }}</div>
        <div class="text-2xl font-bold text-primary">{{ $value }}</div>
    </div>
</div>
