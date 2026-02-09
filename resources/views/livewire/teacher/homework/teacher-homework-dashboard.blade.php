<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-l from-indigo-600 via-purple-600 to-teal-500 bg-clip-text text-transparent">
                        📚 الواجبات المدرسية
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">
                        اختر المادة والشعبة لإدارة واجباتها
                    </p>
                </div>

                <!-- Year Filter -->
                <div class="flex items-center gap-3 bg-white dark:bg-gray-800 px-4 py-2 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <label class="text-sm text-gray-600 dark:text-gray-300">السنة الدراسية:</label>
                    <select wire:model.live="academicYearId" 
                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($this->academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Content -->
            @if(!$this->teacher)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-8 text-center">
                    <div class="text-5xl mb-4">⚠️</div>
                    <h3 class="text-xl font-bold text-yellow-800 dark:text-yellow-200 mb-2">لم يتم ربط حسابك كمعلم</h3>
                    <p class="text-yellow-600 dark:text-yellow-400">يرجى التواصل مع الإدارة لربط حسابك بسجل المعلم.</p>
                </div>
            @elseif($this->courseOfferings->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-xl font-bold text-gray-700 dark:text-gray-200 mb-2">لا توجد مواد مُعيَّنة</h3>
                    <p class="text-gray-500 dark:text-gray-400">لم يتم تعيين أي مواد لك في هذه السنة الدراسية.</p>
                </div>
            @else
                <!-- Grades Grid -->
                <div class="space-y-8">
                    @foreach($this->courseOfferings as $gradeName => $offerings)
                        <div class="space-y-4">
                            <!-- Grade Header -->
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-1 bg-gradient-to-b from-indigo-600 to-purple-600 rounded-full"></div>
                                <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $gradeName }}</h2>
                                <span class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 rounded-full">
                                    {{ $offerings->count() }} {{ $offerings->count() == 1 ? 'شعبة' : 'شعب' }}
                                </span>
                            </div>

                            <!-- Sections Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($offerings as $offering)
                                    @php $stats = $this->getHomeworkStats($offering); @endphp
                                    <div class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                        <!-- Card Header -->
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl">
                                                    📘
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-gray-900 dark:text-white">
                                                        {{ $offering->subject->name ?? 'مادة' }}
                                                    </h3>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $offering->classSection->name ?? 'شعبة' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg">
                                                {{ $offering->classSection->students_count ?? 0 }} طالب
                                            </span>
                                        </div>

                                        <!-- Stats -->
                                        <div class="grid grid-cols-3 gap-3 mb-5">
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                                                <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">إجمالي</div>
                                            </div>
                                            <div class="bg-green-50 dark:bg-green-900/30 rounded-xl p-3 text-center">
                                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['published'] }}</div>
                                                <div class="text-xs text-green-600 dark:text-green-400">منشور</div>
                                            </div>
                                            <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-xl p-3 text-center">
                                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['draft'] }}</div>
                                                <div class="text-xs text-yellow-600 dark:text-yellow-400">مسودة</div>
                                            </div>
                                        </div>

                                        <!-- Action Button -->
                                        <a href="{{ route('grading.homework.index', $offering->id) }}" 
                                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            إدارة الواجبات
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
