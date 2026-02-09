@props([
    'height' => 'h-4',
    'width' => 'w-full',
    'rounded' => 'rounded',
    'count' => 1,
])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-2']) }}>
    @for ($i = 0; $i < $count; $i++)
        <div class="bg-gray-200 dark:bg-gray-700 {{ $height }} {{ $width }} {{ $rounded }}"></div>
    @endfor
</div>
