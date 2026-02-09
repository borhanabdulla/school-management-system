@props([
    'icon',
    'title',
    'description',
    'time' => null,
    'iconBg' => 'bg-indigo-100',
    'iconColor' => 'text-indigo-600'
])

<div class="flex gap-4 items-start py-4 hover:bg-gray-50 rounded-lg transition-colors duration-200 px-2 -mx-2">
    <div class="flex-shrink-0">
        <div class="{{ $iconBg }} {{ $iconColor }} rounded-lg p-2">
            {!! $icon !!}
        </div>
    </div>
    
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900">{{ $title }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ $description }}</p>
        @if($time)
            <p class="text-xs text-gray-400 mt-1">{{ $time }}</p>
        @endif
    </div>
</div>
