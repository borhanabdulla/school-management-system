@props(['enrollments'])

<div class="relative pl-4 sm:pl-0">
    <!-- Timeline Line -->
    <div class="absolute right-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-slate-700 hidden sm:block"></div>

    <div class="space-y-8">
        @foreach ($enrollments as $enrollment)
            <div class="relative flex flex-col sm:flex-row gap-6 group">
                <!-- Dot -->
                <div class="absolute right-6 top-6 h-4 w-4 rounded-full border-2 border-white dark:border-slate-800 shadow-sm z-10 hidden sm:block transition-all duration-300
                    {{ $enrollment->status === 'active' 
                        ? 'bg-purple-600 ring-4 ring-purple-100 dark:ring-purple-900/40 scale-110' 
                        : 'bg-gray-300 dark:bg-slate-600 group-hover:bg-purple-400 group-hover:scale-110' }}">
                </div>

                <!-- Date/Year (Left Side) -->
                <div class="sm:w-32 pt-5 text-right shrink-0">
                    <span class="block text-sm font-bold text-gray-900 dark:text-white">{{ $enrollment->academicYear->name }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ $enrollment->status === 'active' ? 'العام الحالي' : 'مكتمل' }}
                    </span>
                </div>

                <!-- Content Card (Right Side) -->
                <div class="flex-1">
                    <div class="relative rounded-2xl border transition-all duration-300 overflow-hidden
                        {{ $enrollment->status === 'active'
                            ? 'bg-white border-purple-200 shadow-md dark:bg-slate-800 dark:border-purple-900/50'
                            : 'bg-gray-50 border-gray-200 hover:border-purple-200 hover:shadow-sm dark:bg-slate-800/50 dark:border-slate-700 dark:hover:border-slate-600' }}">
                        
                        <!-- Active Indicator Strip -->
                        @if($enrollment->status === 'active')
                            <div class="absolute top-0 right-0 bottom-0 w-1.5 bg-purple-500"></div>
                        @endif

                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        {{ $enrollment->grade->name }}
                                        @if($enrollment->status === 'active')
                                            <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                نشط
                                            </span>
                                        @endif
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                        {{ $enrollment->classSection->name ?? 'غير مسكن في فصل' }}
                                    </p>
                                </div>
                                
                                @if($enrollment->final_grade)
                                    <div class="text-center bg-gray-100 dark:bg-slate-700 rounded-lg p-2 min-w-[60px]">
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">المعدل</span>
                                        <span class="block text-lg font-bold text-gray-900 dark:text-white">{{ $enrollment->final_grade }}%</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
