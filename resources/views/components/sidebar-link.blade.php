@props(['active'])

@php
$classes = ($active ?? false)
            ? 'group flex items-center px-2 py-2 text-sm font-medium rounded-md bg-primary text-white'
            : 'group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
