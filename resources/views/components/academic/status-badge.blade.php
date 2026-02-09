@props(['status'])

@php
    $color = match ($status->value ?? $status) {
        'active' => 'green',
        'pending' => 'yellow',
        'closed' => 'gray',
        'archived' => 'red',
        default => 'gray',
    };

    $label = match ($status->value ?? $status) {
        'active' => 'نشط',
        'pending' => 'مسودة',
        'closed' => 'مغلق',
        'archived' => 'مؤرشف',
        default => $status->value ?? $status,
    };
@endphp

<span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300 border border-{{ $color }}-200 dark:border-{{ $color }}-800">
    <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500 ml-1.5"></span>
    {{ $label }}
</span>
