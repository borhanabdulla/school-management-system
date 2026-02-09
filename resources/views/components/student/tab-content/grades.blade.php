@props(['performanceSummary', 'detailedCourses', 'recommendations'])

<div class="space-y-6">
    <!-- Overall Performance -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg">
            <p class="text-indigo-100 text-sm mb-1">المعدل التراكمي</p>
            <h3 class="text-3xl font-bold">{{ $performanceSummary['overall_average'] ?? 0 }}%</h3>
            <div class="mt-4 h-1.5 bg-black/20 rounded-full overflow-hidden">
                <div class="h-full bg-white/80 rounded-full"
                    style="width: {{ min($performanceSummary['overall_average'] ?? 0, 100) }}%"></div>
            </div>
        </div>

        @if ($performanceSummary['best_performing'] ?? false)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-lg border border-gray-100 dark:border-gray-700">
                <p class="text-gray-500 text-sm mb-1">الأفضل أداءً</p>
                <h3 class="text-xl font-bold text-green-600">
                    {{ $performanceSummary['best_performing']->name }}</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $performanceSummary['best_performing']->score }}%
                </p>
            </div>
        @endif

        @if ($performanceSummary['needs_attention'] ?? false)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-lg border border-gray-100 dark:border-gray-700">
                <p class="text-gray-500 text-sm mb-1">تحتاج متابعة</p>
                <h3 class="text-xl font-bold text-orange-500">
                    {{ $performanceSummary['needs_attention']->name }}</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $performanceSummary['needs_attention']->score }}%
                </p>
            </div>
        @endif
    </div>

    <!-- Detailed Grades -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">تفاصيل المواد الدراسية</h3>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @if ($detailedCourses ?? false)
                @foreach ($detailedCourses as $course)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex justify-between mb-1">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $course->name }}</span>
                                <span class="font-bold {{ $course->score >= 90 ? 'text-green-600' : ($course->score >= 75 ? 'text-blue-600' : 'text-orange-500') }}">
                                    {{ $course->score }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full {{ $course->score >= 90 ? 'bg-green-500' : ($course->score >= 75 ? 'bg-blue-500' : 'bg-orange-500') }}"
                                    style="width: {{ min($course->score, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="p-8 text-center text-gray-500">
                    لا توجد بيانات درجات متاحة
                </div>
            @endif
        </div>
    </div>

    <!-- Recommendations -->
    @if ($recommendations ?? false)
        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800">
            <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-300 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                توصيات وتحليلات
            </h3>
            <ul class="space-y-3">
                @foreach ($recommendations as $recommendation)
                    <li class="flex items-start gap-3 text-indigo-800 dark:text-indigo-200">
                        <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $recommendation }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
