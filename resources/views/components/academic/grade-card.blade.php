@props(['grade'])

@php
    // Determine stage color theme based on rank (matching structure-manager logic)
    $theme = match ($grade->stage->rank) {
        1 => [
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-700',
            'icon_bg' => 'bg-emerald-100',
            'border' => 'border-emerald-200',
            'badge' => 'bg-emerald-500',
        ],
        2 => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-700',
            'icon_bg' => 'bg-blue-100',
            'border' => 'border-blue-200',
            'badge' => 'bg-blue-500',
        ],
        3 => [
            'bg' => 'bg-purple-50',
            'text' => 'text-purple-700',
            'icon_bg' => 'bg-purple-100',
            'border' => 'border-purple-200',
            'badge' => 'bg-purple-500',
        ],
        default => [
            'bg' => 'bg-gray-50',
            'text' => 'text-gray-700',
            'icon_bg' => 'bg-gray-100',
            'border' => 'border-gray-200',
            'badge' => 'bg-gray-500',
        ],
    };
@endphp

<div class="group relative bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-300 hover:-translate-y-1 h-full flex flex-col">
    {{-- Level Badge --}}
    <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full {{ $theme['badge'] }} text-white flex items-center justify-center text-sm font-bold shadow-md z-10">
        {{ $grade->level_order }}
    </div>

    {{-- Header --}}
    <div class="flex justify-between items-start mb-4">
        <div class="w-12 h-12 rounded-xl {{ $theme['bg'] }} {{ $theme['text'] }} dark:bg-gray-700 dark:text-white flex items-center justify-center font-bold text-lg shadow-sm">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $theme['bg'] }} {{ $theme['text'] }} dark:bg-gray-700 dark:text-white">
            {{ $grade->stage->name }}
        </span>
    </div>

    <h4 class="font-bold text-gray-800 dark:text-white text-xl mb-4">{{ $grade->name }}</h4>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->sections_count }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">شعبة</span>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->students_count }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">طالب</span>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->subjects_count }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">مادة</span>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->teachers_count ?? 0 }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">معلم</span>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-auto grid grid-cols-2 gap-3">
        <a href="#" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-xl text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            المناهج
        </a>
        <a href="#" class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 dark:bg-gray-700/50 dark:border-gray-600 dark:text-gray-200 rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            الشعب
        </a>
    </div>
</div>

