@props(['selectedYear'])

@php
    // حساب تقدم السنة ككل بناءً على تواريخها
    $yearProgress = 0;
    $daysRemaining = 0;
    if ($selectedYear->start_date && $selectedYear->end_date) {
        $totalDays = $selectedYear->start_date->diffInDays($selectedYear->end_date);
        $daysPassed = $selectedYear->start_date->diffInDays(now());

        if (now()->lt($selectedYear->start_date)) {
            $yearProgress = 0;
            $daysRemaining = $totalDays;
        } elseif (now()->gt($selectedYear->end_date)) {
            $yearProgress = 100;
            $daysRemaining = 0;
        } else {
            $yearProgress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
            $daysRemaining = now()->diffInDays($selectedYear->end_date);
        }
    }
@endphp

<div
    class="bg-surface dark:bg-gray-900 rounded-2xl p-6 shadow-sm border border-border dark:border-gray-700 transition-colors duration-300">
    <div class="flex justify-between items-end mb-2">
        <div>
            <h3 class="text-lg font-bold text-primary dark:text-white">رحلة السنة الدراسية</h3>
            <p class="text-sm text-secondary dark:text-gray-400 mt-1">
                @if ($yearProgress >= 100)
                    🎉 اكتملت السنة الدراسية!
                @elseif($yearProgress > 0)
                    🚀 نحن في منتصف الطريق، تبقى حوالي <span
                        class="font-bold text-indigo-600 dark:text-indigo-400">{{ $daysRemaining }} يوم</span>
                @else
                    ⏳ لم تبدأ الرحلة بعد
                @endif
            </p>
        </div>
        <div class="text-right">
            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ round($yearProgress) }}%</span>
            <span class="text-xs text-secondary dark:text-gray-500 block">مكتمل</span>
        </div>
    </div>
    <div class="w-full h-3 bg-background dark:bg-gray-800 rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 transition-all duration-1000 ease-out relative"
            style="width: {{ $yearProgress }}%">
            <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
        </div>
    </div>
</div>
