@props(['term'])

@php
    // تحديد حالة الترم وتنسيقه
    $isCompleted = $term->status === \App\Domains\Academic\Term\Enums\TermStatus::Completed
        || ($term->end_date && now()->gt($term->end_date));
    $isActive = $term->status === \App\Domains\Academic\Term\Enums\TermStatus::Active;
    $isPending = $term->status === \App\Domains\Academic\Term\Enums\TermStatus::Pending;
    $isFuture = !$isCompleted && !$isActive;

    $cardTheme = match (true) {
        $isActive => [
            'border' => 'border-indigo-500 ring-4 ring-indigo-500/10',
            'icon_bg' => 'bg-indigo-600',
            'icon' => '🔥',
            'status_text' => 'text-indigo-600',
            'opacity' => 'opacity-100',
            'scale' => 'scale-105 md:-translate-y-2',
            'shadow' => 'shadow-xl shadow-indigo-500/20',
        ],
        $isCompleted => [
            'border' => 'border-green-500',
            'icon_bg' => 'bg-green-500',
            'icon' => '✓',
            'status_text' => 'text-green-600',
            'opacity' => 'opacity-90',
            'scale' => 'scale-100',
            'shadow' => 'shadow-sm',
        ],
        default => [
            'border' => 'border-gray-300 border-dashed',
            'icon_bg' => 'bg-gray-300',
            'icon' => '⏳',
            'status_text' => 'text-gray-500',
            'opacity' => 'opacity-75 grayscale',
            'scale' => 'scale-95',
            'shadow' => 'shadow-none',
        ],
    };

    // حساب تقدم الترم
    $termProgress = 0;
    if ($term->start_date && $term->end_date) {
        $tTotal = $term->start_date->diffInDays($term->end_date);
        $tPassed = $term->start_date->diffInDays(now());
        if (now()->lt($term->start_date)) {
            $termProgress = 0;
        } elseif (now()->gt($term->end_date)) {
            $termProgress = 100;
        } else {
            $termProgress = $tTotal > 0 ? min(100, max(0, ($tPassed / $tTotal) * 100)) : 0;
        }
    }
@endphp

<div class="relative group transition-all duration-300 {{ $cardTheme['scale'] }} {{ $cardTheme['opacity'] }}">
    {{-- نقطة الاتصال على الخط الزمني (للشاشات الكبيرة) --}}
    <div
        class="hidden md:flex absolute -top-10 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full {{ $cardTheme['icon_bg'] }} text-white items-center justify-center font-bold shadow-lg z-20 border-4 border-white dark:border-gray-800">
        {{ $cardTheme['icon'] }}
    </div>

    {{-- بطاقة الترم --}}
    <div
        class="bg-surface dark:bg-gray-900 rounded-2xl p-5 border-2 {{ $cardTheme['border'] }} {{ $cardTheme['shadow'] }} hover:shadow-md transition-all duration-300 h-full flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-3">
                <h4 class="font-bold text-lg text-primary dark:text-white">{{ $term->name }}</h4>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="text-secondary dark:text-gray-400 hover:text-primary dark:hover:text-white p-1 rounded-full hover:bg-background dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute left-0 mt-2 w-40 bg-surface dark:bg-gray-900 rounded-xl shadow-lg border border-border dark:border-gray-700 z-50 py-1">
                        <button wire:click="edit({{ $term->id }})" @click="open = false"
                            class="flex w-full items-center px-4 py-2 text-sm text-primary dark:text-white hover:bg-background dark:hover:bg-gray-800 transition-colors">
                            تعديل
                        </button>
                        @if ($isPending && $term->academicYear->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active)
                            <button wire:click="activateTerm({{ $term->id }})"
                                wire:confirm="تفعيل هذا الفصل؟ سيتم إغلاق الفصول الأخرى." @click="open = false"
                                class="flex w-full items-center px-4 py-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                تفعيل
                            </button>
                        @endif
                        @if (!$isActive)
                            <button wire:click="delete({{ $term->id }})" wire:confirm="حذف هذا الفصل؟"
                                @click="open = false"
                                class="flex w-full items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                حذف
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-2 text-sm text-secondary dark:text-gray-400 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-secondary dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $term->start_date ? $term->start_date->format('d M') : '??' }} -
                        {{ $term->end_date ? $term->end_date->format('d M') : '??' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-secondary dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $term->start_date && $term->end_date ? ceil($term->start_date->diffInWeeks($term->end_date)) : 0 }}
                        أسبوع</span>
                </div>
            </div>
        </div>

        {{-- شريط تقدم الترم --}}
        <div class="mt-auto">
            <div class="flex justify-between text-xs mb-1">
                <span class="{{ $cardTheme['status_text'] }} font-bold">
                    {{ match (true) {$isActive => 'نشط حالياً',$isCompleted => 'مكتمل',default => 'قادم'} }}
                </span>
                <span class="text-secondary dark:text-gray-500">{{ round($termProgress) }}%</span>
            </div>
            <div class="w-full h-1.5 bg-background dark:bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full {{ $isActive ? 'bg-indigo-500' : ($isCompleted ? 'bg-green-500' : 'bg-gray-400') }}"
                    style="width: {{ $termProgress }}%"></div>
            </div>
        </div>
    </div>
</div>
