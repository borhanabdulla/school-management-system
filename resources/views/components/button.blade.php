@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button'
])

@php
    $baseClasses = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

    $variants = [
        'primary' => 'bg-primary text-white hover:bg-blue-700 focus:ring-blue-500', // Fallback hover/ring colors if variables aren't perfect for them, or use opacity
        // Using opacity modifiers with CSS variables requires the variable to be just the channels (defined in Level 1)
        // In Level 1 we defined --color-primary: 37 99 235; so we can use bg-primary (which is rgb(var(--color-primary) / <alpha>))
        // For hover, we can use bg-primary/90.
        'primary' => 'bg-primary text-white hover:bg-primary/90 focus:ring-primary/50',
        'secondary' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 focus:ring-secondary/50',
        'success' => 'bg-success text-white hover:bg-success/90 focus:ring-success/50',
        'warning' => 'bg-warning text-white hover:bg-warning/90 focus:ring-warning/50',
        'danger' => 'bg-danger text-white hover:bg-danger/90 focus:ring-danger/50',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 focus:ring-slate-500/50',
    ];

    $sizes = [
        'sm' => 'h-8 px-3 text-xs',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-12 px-6 text-base',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}>
    {{ $slot }}
</button>
