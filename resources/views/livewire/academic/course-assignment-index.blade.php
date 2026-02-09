<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="min-h-screen">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-l from-indigo-600 to-purple-600 bg-clip-text text-transparent dark:from-indigo-400 dark:to-purple-400">
                    تعيين المواد للمعلمين
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    السنة الدراسية: <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $academicYearName }}</span>
                </p>
            </div>
            <!-- Stats Cards -->
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">المعروضة</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $this->stats['filtered_sections'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">الإجمالي</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $this->stats['total_sections'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">بحث سريع</label>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text" 
                           class="w-full pr-10 pl-3 py-2.5 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                           placeholder="اسم الشعبة أو الصف...">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Educational Stage Filter -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">المرحلة الدراسية</label>
                <select wire:model.live="stageId" 
                        class="w-full px-3 py-2.5 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <option value="">كل المراحل</option>
                    @foreach($this->stages as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grade Filter -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">الصف الدراسي</label>
                <select wire:model.live="gradeId" 
                        class="w-full px-3 py-2.5 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all disabled:bg-gray-100 dark:disabled:bg-gray-600 disabled:text-gray-400"
                        @if(!$stageId) disabled @endif>
                    <option value="">كل الصفوف</option>
                    @foreach($this->grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            <div class="flex items-end">
                <button wire:click="resetFilters" 
                        class="w-full px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    إعادة تعيين
                </button>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading wire:target="search,stageId,gradeId" class="mt-3">
            <div class="flex items-center gap-2 text-sm text-indigo-600 dark:text-indigo-400">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                جاري التحديث...
            </div>
        </div>
    </div>

    <!-- Sections Grid -->
    @if($this->sections->isEmpty())
        <div class="text-center py-16">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">لا توجد نتائج</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">
                @if($search || $stageId || $gradeId)
                    لا توجد شعب دراسية تطابق الفلاتر المحددة
                @else
                    لا توجد شعب دراسية في هذه السنة الدراسية
                @endif
            </p>
            @if(!$search && !$stageId && !$gradeId)
                <a href="{{ route('class-sections.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-l from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    إدارة الشعب الدراسية
                </a>
            @else
                <button wire:click="resetFilters" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-xl transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    إعادة تعيين الفلاتر
                </button>
            @endif
        </div>
    @else
        <div class="space-y-8">
            @foreach($this->sections as $stageName => $stageSections)
                <div>
                    <!-- Stage Header -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $stageName }}</h2>
                        <div class="flex-1 h-px bg-gradient-to-l from-transparent via-gray-200 to-transparent dark:via-gray-700"></div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                            {{ $stageSections->count() }} شعبة
                        </span>
                    </div>

                    <!-- Sections Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($stageSections as $section)
                            <a href="{{ route('course-assignment.show', $section->id) }}" wire:navigate
                               class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/10 hover:border-indigo-200 dark:hover:border-indigo-800 hover:-translate-y-1">
                                <!-- Card Content -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-800 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors text-lg">
                                            {{ $section->name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $section->grade->name }}
                                        </p>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center group-hover:from-indigo-50 group-hover:to-purple-50 dark:group-hover:from-indigo-900/50 dark:group-hover:to-purple-900/50 transition-all duration-300">
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Stats Row -->
                                @php
                                    $genderType = $section->gender_type ?? 'mixed';
                                    $genderLabel = match($genderType) {
                                        'male' => 'بنين',
                                        'female' => 'بنات',
                                        default => 'مختلطة'
                                    };
                                    $genderColor = match($genderType) {
                                        'male' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                        'female' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/50 dark:text-pink-300',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                    };
                                @endphp
                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-lg {{ $genderColor }}">
                                        {{ $genderLabel }}
                                    </span>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                                        تعيين المواد ←
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
    </div>
</div>
