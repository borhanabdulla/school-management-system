<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Classes Today -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow group">
        <div class="flex items-center justify-between mb-4">
            <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 group-hover:bg-blue-100 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-lg">اليوم</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $totalClasses }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">حصص دراسية</p>
    </div>

    <!-- Attendance Rate -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow group">
        <div class="flex items-center justify-between mb-4">
            <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 group-hover:bg-green-100 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            @if($attendanceRate > 0)
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-lg">نشط</span>
            @endif
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $attendanceRate }}%</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">نسبة الحضور اليوم</p>
    </div>

    <!-- Pending Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow group">
        <div class="flex items-center justify-between mb-4">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 group-hover:bg-amber-100 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            @if($pendingClasses > 0)
                <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-lg">مهم</span>
            @else
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-lg">مكتمل</span>
            @endif
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $pendingClasses }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">حصص بانتظار الرصد</p>
    </div>
</div>
