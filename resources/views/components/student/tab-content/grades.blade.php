@props(['performanceSummary', 'detailedCourses', 'recommendations'])

<div class="space-y-6">
    <!-- Overall Performance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Main GPA Card -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 text-white shadow-lg">
            <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>
            <div class="absolute bottom-0 left-0 -ml-4 -mb-4 h-24 w-24 rounded-full bg-black/10 blur-xl"></div>
            
            <div class="relative">
                <p class="text-indigo-100 text-sm font-medium mb-2">المعدل التراكمي</p>
                <div class="flex items-end gap-2">
                    <h3 class="text-4xl font-bold tracking-tight">{{ $performanceSummary['overall_average'] ?? 0 }}%</h3>
                    <span class="mb-1 text-sm text-indigo-200">النتيجة النهائية</span>
                </div>
                
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-indigo-200 mb-1">
                        <span>التقدم</span>
                        <span>{{ min($performanceSummary['overall_average'] ?? 0, 100) }}%</span>
                    </div>
                    <div class="h-2 w-full bg-black/20 rounded-full overflow-hidden">
                        <div class="h-full bg-white/90 rounded-full transition-all duration-1000 ease-out"
                             style="width: {{ min($performanceSummary['overall_average'] ?? 0, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Subject -->
        @if ($performanceSummary['best_performing'] ?? false)
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-gray-100 dark:bg-slate-800 dark:border-slate-700 hover:shadow-md transition-all">
                <div class="absolute top-0 right-0 h-1 w-full bg-emerald-500"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">الأفضل أداءً</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ $performanceSummary['best_performing']->name }}
                        </h3>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ $performanceSummary['best_performing']->score }}%
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">ممتاز</span>
                </div>
            </div>
        @endif

        <!-- Needs Attention -->
        @if ($performanceSummary['needs_attention'] ?? false)
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-gray-100 dark:bg-slate-800 dark:border-slate-700 hover:shadow-md transition-all">
                <div class="absolute top-0 right-0 h-1 w-full bg-orange-500"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">تحتاج متابعة</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ $performanceSummary['needs_attention']->name }}
                        </h3>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-orange-500 dark:text-orange-400">
                        {{ $performanceSummary['needs_attention']->score }}%
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">يحتاج تحسين</span>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detailed Grades List -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">تفاصيل المواد الدراسية</h3>
            </div>
            
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                @if ($detailedCourses ?? false)
                    @foreach ($detailedCourses as $course)
                        <div class="group p-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg flex items-center justify-center
                                        {{ $course->score >= 90 ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 
                                           ($course->score >= 75 ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' : 
                                           'bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400') }}">
                                        <span class="text-xs font-bold">{{ mb_substr($course->name, 0, 1) }}</span>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $course->name }}</span>
                                </div>
                                <span class="font-bold text-lg {{ $course->score >= 90 ? 'text-emerald-600 dark:text-emerald-400' : ($course->score >= 75 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-500 dark:text-orange-400') }}">
                                    {{ $course->score }}%
                                </span>
                            </div>
                            
                            <div class="w-full bg-gray-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $course->score >= 90 ? 'bg-emerald-500' : ($course->score >= 75 ? 'bg-blue-500' : 'bg-orange-500') }}"
                                     style="width: {{ min($course->score, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-8">
                        <x-ui.empty-state icon="document" title="لا توجد درجات" description="لم يتم رصد درجات لهذه الفترة بعد." />
                    </div>
                @endif
            </div>
        </div>

        <!-- Recommendations Column -->
        <div class="lg:col-span-1">
            @if ($recommendations && count($recommendations) > 0)
                <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800 h-full">
                    <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-300 mb-6 flex items-center gap-2">
                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        توصيات وتحليلات
                    </h3>
                    
                    <ul class="space-y-4">
                        @foreach ($recommendations as $recommendation)
                            <li class="flex gap-3 bg-white dark:bg-slate-800 p-3 rounded-xl shadow-sm border border-indigo-100 dark:border-indigo-900/50">
                                <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $recommendation }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
