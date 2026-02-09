@props([
    'name',
    'label' => null,
    'type' => 'text',
])

@php
    $hasError = $errors->has($name);
    
    $baseClasses = 'w-full rounded-md shadow-sm sm:text-sm transition-colors focus:ring-2 focus:ring-offset-0 disabled:opacity-50 disabled:pointer-events-none';
    
    $defaultClasses = 'border-[rgb(var(--form-input-border))] focus:border-primary focus:ring-primary/20';
    $errorClasses = 'border-danger text-danger placeholder-danger/50 focus:border-danger focus:ring-danger/20';
    
    $classes = $baseClasses . ' ' . ($hasError ? $errorClasses : $defaultClasses);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-[rgb(var(--form-label))] mb-1">
            {{ $label }}
        </label>
    @endif

    <input 
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge(['class' => $classes]) }}
    />

    @error($name)
        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
