@props([
    'label',
    'value',
    'icon' => null,
    'gradient' => 'purple-blue',
    'trend' => null,
    'trendValue' => null
])

@php
    $gradientClasses = [
        'purple-blue' => 'gradient-purple-blue',
        'green-teal' => 'gradient-green-teal',
        'orange-pink' => 'gradient-orange-pink',
        'red-purple' => 'gradient-red-purple',
    ];
    
    $trendColors = [
        'up' => 'text-green-500',
        'down' => 'text-red-500',
        'neutral' => 'text-gray-500',
    ];
    
    $gradientClass = $gradientClasses[$gradient] ?? 'gradient-purple-blue';
@endphp

<div class="stat-card {{ $gradientClass }} group">
    <div class="relative z-10">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-white/80 text-sm font-medium mb-1">{{ $label }}</p>
                <p class="text-white text-3xl font-bold font-display count-up">{{ $value }}</p>
                
                @if($trend && $trendValue)
                    <div class="mt-2 flex items-center gap-1 text-white/90">
                        @if($trend === 'up')
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($trend === 'down')
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        <span class="text-sm font-medium">{{ $trendValue }}</span>
                    </div>
                @endif
            </div>
            
            @if($icon)
                <div class="icon-gradient bg-white/20 group-hover:bg-white/30 transition-all duration-300">
                    {!! $icon !!}
                </div>
            @endif
        </div>
    </div>
</div>
