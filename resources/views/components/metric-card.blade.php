@props([
    'label' => '',
    'value' => '',
    'icon' => null,
    'iconBg' => 'bg-indigo-100',
    'iconColor' => 'text-indigo-600',
    'trend' => null,
    'trendValue' => null,
    'comparison' => null,
])

<div class="modern-card p-5 hover:shadow-xl transition-all duration-300">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm text-gray-600 mb-1">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ $value }}</p>
            
            @if($trend && $trendValue)
                <div class="flex items-center gap-1">
                    @if($trend === 'up')
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-green-600 font-medium">{{ $trendValue }}</span>
                    @elseif($trend === 'down')
                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-red-600 font-medium">{{ $trendValue }}</span>
                    @else
                        <span class="text-sm text-gray-600 font-medium">{{ $trendValue }}</span>
                    @endif
                </div>
            @endif
            
            @if($comparison)
                <p class="text-xs text-gray-500 mt-1">{{ $comparison }}</p>
            @endif
        </div>
        
        @if($icon)
            <div class="{{ $iconBg }} {{ $iconColor }} rounded-xl p-3">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
