<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-l from-indigo-600 via-purple-600 to-teal-500 bg-clip-text text-transparent">
                        إدارة الواجبات
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">
                        المادة: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $this->courseOffering->subject->name }}</span> - 
                        الشعبة: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $this->courseOffering->classSection->name }}</span>
                    </p>
                </div>

                <button wire:click="create"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة واجب جديد
                </button>
            </div>

            <!-- Content -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($this->homeworks as $homework)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-{{ $homework->status->color() }}-100 text-{{ $homework->status->color() }}-800 dark:bg-{{ $homework->status->color() }}-900/30 dark:text-{{ $homework->status->color() }}-300">
                                {{ $homework->status->label() }}
                            </span>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $homework->id }})" class="text-gray-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $homework->id }})" wire:confirm="هل أنت متأكد من حذف هذا الواجب؟" class="text-gray-400 hover:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $homework->title }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 line-clamp-2">{{ $homework->description }}</p>

                        @if($homework->attachment_path)
                            <a href="{{ Storage::url($homework->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline mb-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                عرض المرفق
                            </a>
                        @endif

                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>يستحق في: {{ $homework->due_date ? $homework->due_date->format('Y-m-d H:i') : 'غير محدد' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>{{ $homework->submission_type->label() }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-500">الدرجة: {{ $homework->max_score }}</span>
                            <!-- Link to Grader -->
                            <a href="{{ route('grading.homework.grade', $homework->id) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">
                                رصد الدرجات &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">لا توجد واجبات</h3>
                        <p class="text-gray-500 dark:text-gray-400">ابدأ بإضافة واجب جديد لهذه المادة.</p>
                    </div>
                @endforelse
            </div>

            <!-- Modal -->
            @if($showModal)
                <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">
                                    {{ $isEditing ? 'تعديل الواجب' : 'إضافة واجب جديد' }}
                                </h3>
                                
                                <div class="space-y-4">
                                    <!-- Title -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان الواجب</label>
                                        <input type="text" wire:model="title" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الوصف</label>
                                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Attachment -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مرفق (اختياري)</label>
                                        <input type="file" wire:model="attachment" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-300">
                                        <div wire:loading wire:target="attachment" class="text-xs text-indigo-600 mt-1">جاري الرفع...</div>
                                        @error('attachment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        
                                        @if($existingAttachment && !$attachment)
                                            <div class="mt-2 text-sm text-gray-500">
                                                <a href="{{ Storage::url($existingAttachment) }}" target="_blank" class="text-indigo-600 hover:underline">عرض المرفق الحالي</a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Submission Type -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع التسليم</label>
                                            <select wire:model="submission_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                @foreach(\App\Domains\Academic\Homework\Enums\SubmissionType::cases() as $type)
                                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                                @endforeach
                                            </select>
                                            @error('submission_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Status -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الحالة</label>
                                            <select wire:model="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                @foreach(\App\Domains\Academic\Homework\Enums\HomeworkStatus::cases() as $status)
                                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Due Date -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاريخ الاستحقاق</label>
                                            <input type="datetime-local" wire:model="due_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Max Score -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الدرجة العظمى</label>
                                            <input type="number" step="0.5" wire:model="max_score" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('max_score') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- Assessment Link -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ربط بوعاء درجات (اختياري)</label>
                                        <select wire:model="assessment_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- بدون ربط --</option>
                                            @foreach($this->assessments as $assessment)
                                                <option value="{{ $assessment->id }}">{{ $assessment->title }} ({{ $assessment->max_score }})</option>
                                            @endforeach
                                        </select>
                                        @error('assessment_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Allow Late -->
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="allow_late" id="allow_late" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label for="allow_late" class="mr-2 block text-sm text-gray-900 dark:text-gray-300">
                                            السماح بالتسليم المتأخر
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    حفظ
                                </button>
                                <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    إلغاء
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Flash Message -->
            </div>
    </div>
</div>
