<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-5" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('course-offerings.index') }}" wire:navigate 
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                        <svg class="w-4 h-4 ml-2 rtl:ml-0 rtl:mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        تعيين المواد
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400 rotate-180" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="mr-2 rtl:mr-0 rtl:ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $sectionName }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black bg-gradient-to-l from-indigo-600 via-purple-600 to-teal-500 bg-clip-text text-transparent">
                    توزيع المواد الدراسية
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">
                    الشعبة: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $sectionName }}</span>
                </p>
            </div>

            <!-- Back Button - سهم صحيح في RTL -->
            <a href="{{ route('course-offerings.index') }}" wire:navigate
               class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300">
                <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Progress Summary -->
    @if(!empty($subjects))
        <div class="mb-6 p-5 bg-gradient-to-l from-indigo-500/10 to-purple-500/10 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl border border-indigo-200 dark:border-indigo-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ collect($assignments ?? [])->filter()->count() }} / {{ count($subjects) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">تم تعيين المعلمين</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">احرص على تعيين الجميع قبل بدء الفصل</p>
                    </div>
                </div>
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden">
        <!-- Card Header -->
        <div class="px-8 py-6 bg-gradient-to-l from-indigo-50/50 via-purple-50/30 to-white dark:from-gray-900 dark:to-gray-800 border-b border-gray-100 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-2xl shadow-indigo-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">المواد الدراسية</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">اختر معلمًا لكل مادة - يتم الحفظ تلقائيًا</p>
                    </div>
                </div>

                <!-- Search -->
                @if(!empty($subjects))
                    <div class="relative max-w-sm w-full">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="ابحث عن مادة..." 
                               class="w-full pr-12 pl-5 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-2xl focus:ring-4 focus:ring-indigo-500/30 focus:border-indigo-500 text-sm">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            @if(empty($subjects) || count($filteredSubjects ?? $subjects) === 0)
                <!-- Enhanced Empty State -->
                <div class="text-center py-20">
                    <div class="w-32 h-32 mx-auto mb-8 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center shadow-2xl">
                        <svg class="w-16 h-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300 mb-3">لا توجد مواد دراسية</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-10">لم يتم إضافة مواد دراسية لهذه الشعبة بعد، أو لا تطابق نتائج البحث.</p>
                    <a href="{{ route('subject-manager.index') }}" wire:navigate
                       class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-l from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-2xl shadow-2xl hover:shadow-indigo-500/50 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        إدارة المواد والمناهج
                    </a>
                </div>
            @else
                <!-- Subjects Grid / List -->
                <div class="grid gap-5 lg:grid-cols-1">
                    @foreach($filteredSubjects ?? $subjects as $subject)
                        @php
                            $assignedTeacher = $this->teachers->firstWhere('id', $assignments[$subject['subject_id']] ?? null);
                        @endphp
                        <div class="group relative bg-gradient-to-l from-gray-50/50 to-white dark:from-gray-900/50 dark:to-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-2xl hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-300">
                            <!-- Loading Overlay for this card -->
                            <div wire:loading wire:target="updateAssignment({{ $subject['subject_id'] }})" class="absolute inset-0 bg-white/70 dark:bg-gray-900/70 rounded-2xl flex items-center justify-center z-10">
                                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                                <!-- Subject Info -->
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/40 dark:to-purple-900/40 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                        <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $subject['subject_name'] }}</h4>
                                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            {{ $subject['subject_code'] }}
                                        </div>
                                        @if($assignedTeacher)
                                            <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                معين: {{ $assignedTeacher['name'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Teacher Select -->
                                <div class="w-full sm:w-80">
                                    <select 
                                        wire:model.live="assignments.{{ $subject['subject_id'] }}"
                                        wire:change="updateAssignment({{ $subject['subject_id'] }})"
                                        class="block w-full px-5 py-4 pr-12 text-base bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 rounded-2xl shadow-sm transition-all duration-200 appearance-none cursor-pointer font-medium"
                                        style="background-image: url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e\"); background-position: left 1.5rem center; background-repeat: no-repeat; background-size: 1.2em;"
                                    >
                                        {{-- ✅ PR-2: الخيار الفارغ disabled لأن المعلم مطلوب --}}
                                        <option value="" disabled {{ empty($assignments[$subject['subject_id']]) ? 'selected' : '' }}>-- اختر معلماً (مطلوب) --</option>
                                        @foreach($this->teachers as $teacher)
                                            <option value="{{ $teacher['id'] }}">{{ $teacher['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Help Card -->
    <div class="mt-8 p-6 bg-gradient-to-l from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border border-blue-200 dark:border-blue-800 rounded-3xl">
        <div class="flex items-start gap-5">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-lg font-bold text-blue-800 dark:text-blue-300 mb-2">تلميح سريع</h4>
                <p class="text-blue-700 dark:text-blue-400 leading-relaxed">التغييرات تُحفظ تلقائياً فور اختيار المعلم. <strong>المعلم مطلوب لكل مادة</strong> - للتغيير، اختر معلماً آخر. تأكد من تعيين جميع المواد لتجنب أي تعارض في الجدول الدراسي.</p>
            </div>
        </div>
    </div>
</div>        </div>
    </div>
</div>

<!-- إضافة في الـ Component (Livewire) -->
@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('toast', (event) => {
            // يمكنك استبدال هذا بـ Toast library مثل Toastify أو HotToast
            alert(event.message); // مؤقت
        });
    });
</script>
@endpush