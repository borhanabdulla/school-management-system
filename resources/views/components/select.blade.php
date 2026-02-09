@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => null,
])

@php
    $hasError = $errors->has($name);
    
    $baseClasses = 'w-full rounded-md shadow-sm sm:text-sm transition-colors focus:ring-2 focus:ring-offset-0 disabled:opacity-50 disabled:pointer-events-none';
    
    $defaultClasses = 'border-slate-300 focus:border-primary focus:ring-primary/20';
    $errorClasses = 'border-danger text-danger focus:border-danger focus:ring-danger/20';
    
    $classes = $baseClasses . ' ' . ($hasError ? $errorClasses : $defaultClasses);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1">
            {{ $label }}
        </label>
    @endif

    <select 
        name="{{ $name }}" 
        id="{{ $name }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if ($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $value => $text)
            <option value="{{ $value }}" {{ old($name) == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
