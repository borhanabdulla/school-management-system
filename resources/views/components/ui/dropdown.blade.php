@props(['align' => 'left'])

@php
$alignmentClasses = [
    'left' => 'origin-top-left left-0',
    'right' => 'origin-top-right right-0',
];
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 mt-2 w-48 rounded-md shadow-lg {{ $alignmentClasses[$align] }}"
         style="display: none;">
        <div class="rounded-md bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 py-1">
            {{ $slot }}
        </div>
    </div>
</div>
