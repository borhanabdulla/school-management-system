@props(['stats'])

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach ($stats as $stat)
        @php
            $variant = $stat['variant'] ?? 'neutral';
            $variants = [
                'primary' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400',
                'info' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                'success' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
                'danger' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                'neutral' => 'bg-gray-50 dark:bg-gray-900/20 text-gray-600 dark:text-gray-400',
            ];
            $classes = $variants[$variant] ?? $variants['neutral'];
        @endphp
        <div
            class="bg-surface rounded-xl p-5 shadow-sm border border-border hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground mb-1">{{ $stat['title'] }}</p>
                    <p class="text-xl font-bold text-foreground">{{ $stat['value'] }}</p>
                </div>
                <div
                    class="p-3 rounded-lg {{ $classes }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $stat['icon'] }}" />
                    </svg>
                </div>
            </div>
        </div>
    @endforeach
</div>
