@props(['grade'])

<div class="relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all shadow-sm hover:shadow-lg flex flex-col h-full"
     x-data="{ showSubjects: false }"
     @click.outside="showSubjects = false">
    
    {{-- Header --}}
    <div class="flex items-start justify-between mb-4">
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $grade->name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $grade->stage->name }}</p>
        </div>
        <div class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-bold">
            المستوى {{ $grade->level_order }}
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->sections_count ?? 0 }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">شعبة</span>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->subjects_count ?? 0 }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">مادة</span>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->students_count ?? 0 }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">طالب</span>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center border border-gray-100 dark:border-gray-600">
            <span class="block text-lg font-bold text-gray-800 dark:text-white">{{ $grade->teachers_count ?? 0 }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">معلم</span>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-auto relative">
        <button @click="showSubjects = !showSubjects" 
                type="button"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-xl text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            المناهج ({{ $grade->subjects_count ?? 0 }})
            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showSubjects }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        
        {{-- زر الشعب مخفي مؤقتاً - يمكن إظهاره لاحقاً عند الحاجة --}}
        {{-- 
        <a href="#" class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 dark:bg-gray-700/50 dark:border-gray-600 dark:text-gray-200 rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            الشعب
        </a>
        --}}

        {{-- Subjects Dropdown --}}
        <div x-show="showSubjects"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto"
             style="display: none;">
            
            @if($grade->subjects && $grade->subjects->isNotEmpty())
                <div class="p-3">
                    <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 px-2">
                        المواد الدراسية ({{ $grade->subjects->count() }})
                    </div>
                    
                    @foreach($grade->subjects as $subject)
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white block">
                                        {{ $subject->name }}
                                    </span>
                                    @if($subject->code)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $subject->code }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @if($subject->pivot->credit_hours)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $subject->pivot->credit_hours }} ساعة
                                    </span>
                                @endif
                                
                                @if($subject->pivot->term_type)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $subject->pivot->term_type }}
                                    </span>
                                @endif
                                
                                @if($subject->pivot->is_active)
                                    <span class="w-2 h-2 rounded-full bg-green-500" title="نشط"></span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        لا توجد مواد مسندة لهذا الصف
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
