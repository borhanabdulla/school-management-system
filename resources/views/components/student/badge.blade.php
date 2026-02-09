@props(['status', 'label' => null, 'size' => 'md'])

@php
    $statusValue = strtolower($status instanceof \BackedEnum ? $status->value : $status);
    
    $variantMap = [
        // Success
        'active' => 'success',
        'paid' => 'success',
        'graduated' => 'info', // Or success? Usually graduated is distinct. Let's use info or a specific one. User said success|warning|danger|info|neutral.
        
        // Warning
        'partial' => 'warning',
        
        // Danger
        'suspended' => 'danger',
        'unpaid' => 'danger',
        'expelled' => 'danger',
        
        // Neutral
        'withdrawn' => 'neutral',
        'inactive' => 'neutral',
    ];

    $variant = $variantMap[$statusValue] ?? 'neutral';
@endphp

<x-ui.badge :variant="$variant" :label="$label ?? $slot" {{ $attributes }} />
