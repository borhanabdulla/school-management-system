<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm rounded-xl overflow-hidden transition-all duration-300 hover:shadow-md']) }}>
    @if(isset($title))
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ $title }}</h2>
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
