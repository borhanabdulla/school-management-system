@props(['name', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'w-4 h-4',
        'md' => 'w-6 h-6',
        'lg' => 'w-8 h-8',
        'xl' => 'w-10 h-10',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center ' . $sizeClass]) }}>
    @includeIf("icons.{$name}")
</span>
