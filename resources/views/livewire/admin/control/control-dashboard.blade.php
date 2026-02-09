<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-l from-red-600 via-orange-600 to-yellow-500 bg-clip-text text-transparent">
                        لوحة الكنترول
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">
                        إدارة الدورات الامتحانية والأرقام السرية
                    </p>
                </div>

                <button wire:click="create"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    دورة امتحانية جديدة
                </button>
            </div>

            <!-- Flash Messages -->
            <!-- Sessions Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($this->sessions as $session)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 {{ $session->is_active ? 'border-green-500' : 'border-gray-200 dark:border-gray-700' }} p-6 shadow-sm hover:shadow-md transition-shadow">
                        <!-- Status Badge -->
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                {{ $session->status === 'setup' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $session->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $session->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $session->status === 'published' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $session->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ match($session->status) {
                                    'setup' => 'إعداد',
                                    'active' => 'نشط',
                                    'processing' => 'معالجة',
                                    'published' => 'منشور',
                                    'closed' => 'مغلق',
                                    default => $session->status
                                } }}
                            </span>
                            @if($session->is_active)
                                <span class="flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                            @endif
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $session->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $session->academicYear->name }} - {{ $session->term->name }}
                        </p>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-4 text-center">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $session->students_count }}</div>
                                <div class="text-xs text-gray-500">طالب</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $session->marks_entered_count }}</div>
                                <div class="text-xs text-gray-500">درجة مرصودة</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2">
                            @if($session->isSetup())
                                <button wire:click="generateNumbers({{ $session->id }})" 
                                        wire:confirm="سيتم توليد أرقام جلوس وأرقام سرية لجميع الطلاب. هل أنت متأكد؟"
                                        class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    توليد الأرقام
                                </button>
                                <button wire:click="activate({{ $session->id }})"
                                        class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    تفعيل
                                </button>
                            @endif

                            {{-- Print Links - Available when students are seated --}}
                            @if($session->students_count > 0)
                                <div class="w-full flex gap-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <a href="{{ route('control.print', [$session->id, 'seating']) }}" target="_blank"
                                       class="flex-1 px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition-colors text-center">
                                        🖨️ كشف الجلوس
                                    </a>
                                    <a href="{{ route('control.print', [$session->id, 'secret']) }}" target="_blank"
                                       class="flex-1 px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition-colors text-center">
                                        🏷️ ملصقات سرية
                                    </a>
                                </div>
                            @endif

                            @if($session->isActive())
                                <a href="{{ route('control.grading', $session->id) }}"
                                   class="flex-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors text-center">
                                    🔐 رصد الدرجات
                                </a>
                                <button wire:click="processResults({{ $session->id }})"
                                        wire:confirm="سيتم معالجة جميع النتائج (دمج أعمال السنة + الكنترول). هل أنت متأكد؟"
                                        class="flex-1 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    ⚙️ معالجة النتائج
                                </button>
                            @endif

                            @if($session->isProcessing())
                                <button wire:click="processResults({{ $session->id }})"
                                        class="flex-1 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    ⚙️ إعادة المعالجة
                                </button>
                                <button wire:click="publishResults({{ $session->id }})"
                                        wire:confirm="سيتم نشر النتائج وجعلها مرئية للطلاب. هل أنت متأكد؟"
                                        class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    📢 نشر النتائج
                                </button>
                            @endif

                            @if($session->isPublished())
                                <a href="{{ route('control.results', $session->id) }}"
                                   class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors text-center">
                                    📊 عرض النتائج
                                </a>
                                <button wire:click="openPrintModal({{ $session->id }})"
                                   class="flex-1 px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-xl transition-colors text-center">
                                    🎓 الشهادات
                                </button>
                                <a href="{{ route('control.results.holds', $session->id) }}"
                                   class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors text-center">
                                    🚫 حجب
                                </a>
                            @endif

                            @if(!$session->isClosed() && !$session->isPublished())
                                <button wire:click="closeSession({{ $session->id }})"
                                        wire:confirm="هل أنت متأكد من إغلاق هذه الدورة؟"
                                        class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                    إغلاق
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">لا توجد دورات امتحانية</h3>
                        <p class="text-gray-500 dark:text-gray-400">ابدأ بإنشاء دورة امتحانية جديدة.</p>
                    </div>
                @endforelse
            </div>

            <!-- Print Modal -->
            @if($showPrintModal)
                <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showPrintModal', false)"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    طباعة الشهادات
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الفصل</label>
                                        <select wire:model.live="selectedClassSectionId"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                            <option value="">-- جميع الفصول --</option>
                                            @foreach($this->classSections as $class)
                                                <option value="{{ $class->id }}">{{ $class->grade->name }} - {{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <a href="{{ route('control.print.report-cards', ['sessionId' => $selectedSessionId ?? 0]) }}?class_section_id={{ $selectedClassSectionId }}" target="_blank"
                                   class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 sm:ml-3">
                                    طباعة
                                </a>
                                <button type="button" wire:click="$set('showPrintModal', false)"
                                        class="mt-3 w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0">
                                    إلغاء
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Create Modal -->
            @if($showCreateModal)
                <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showCreateModal', false)"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    دورة امتحانية جديدة
                                </h3>
                                
                                <div class="space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اسم الدورة</label>
                                        <input type="text" wire:model="name" placeholder="مثال: اختبارات الفصل الأول 2024"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Academic Year -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">السنة الدراسية</label>
                                        <select wire:model.live="academic_year_id"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                            <option value="">-- اختر --</option>
                                            @foreach($this->academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Term -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الترم</label>
                                        <select wire:model="term_id"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                {{ !$academic_year_id ? 'disabled' : '' }}>
                                            <option value="">-- اختر --</option>
                                            @foreach($this->terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('term_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Start Date -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاريخ البدء</label>
                                            <input type="date" wire:model="start_date"
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        </div>

                                        <!-- End Date -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاريخ الانتهاء</label>
                                            <input type="date" wire:model="end_date"
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" wire:click="save"
                                        class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3">
                                    حفظ
                                </button>
                                <button type="button" wire:click="$set('showCreateModal', false)"
                                        class="mt-3 w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0">
                                    إلغاء
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
