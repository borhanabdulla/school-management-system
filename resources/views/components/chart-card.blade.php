@props([
    'title' => '',
    'subtitle' => '',
])

<div class="modern-card-elevated">
    <div class="p-6">
        @if($title || $subtitle)
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
                {{ $actions ?? '' }}
            </div>
        @endif
        
        <div class="chart-container">
            {{ $slot }}
        </div>
    </div>
</div>
