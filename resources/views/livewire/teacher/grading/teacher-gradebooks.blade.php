<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-pink-600">
                    دفاتر الدرجات
                </span>
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">اختر الدفتر لإدخال درجات الطلاب</p>
        </div>
        
        <!-- Year Filter -->
        <div class="flex items-center gap-3">
            <label class="text-sm text-gray-600 dark:text-gray-300">السنة الدراسية:</label>
            <select wire:model.live="academicYearId" 
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                @foreach($this->academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Gradebooks Grid -->
    @forelse($this->gradebooks as $gradeName => $offerings)
        <div class="space-y-4">
            <!-- Grade Header -->
            <div class="flex items-center gap-3">
                <div class="h-8 w-1 bg-gradient-to-b from-purple-600 to-pink-600 rounded-full"></div>
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">{{ $gradeName }}</h3>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($offerings as $offering)
                    <div class="group relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                        <!-- Decorative Gradient Blob -->
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl group-hover:bg-purple-500/20 transition-all"></div>
                        
                        <!-- Card Content -->
                        <div class="relative z-10">
                            <!-- Subject & Section -->
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ $offering->subject->name ?? 'مادة' }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $offering->classSection->name ?? 'شعبة' }}
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300 rounded-full">
                                    {{ $offering->classSection->students_count ?? 0 }} طالب
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            @php $progress = $this->getProgress($offering); @endphp
                            <div class="mb-4">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>نسبة الإنجاز</span>
                                    <span>{{ $progress }}%</span>
                                </div>
                                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-500" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <a href="{{ route('grading.gradebook.show', $offering->id) }}" 
                                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    دفتر الدرجات
                                </a>
                                <a href="{{ route('grading.homework.index', $offering->id) }}" 
                                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    الواجبات
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg p-12 text-center">
            <div class="text-6xl mb-4">📚</div>
            <h3 class="text-xl font-bold text-gray-700 dark:text-gray-200 mb-2">لا توجد دفاتر درجات</h3>
            <p class="text-gray-500 dark:text-gray-400">لم يتم تعيين أي مواد لك في هذه السنة الدراسية</p>
        </div>
    @endforelse
</div>

