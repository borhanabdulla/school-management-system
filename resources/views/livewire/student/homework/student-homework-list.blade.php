<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-l from-indigo-600 via-purple-600 to-teal-500 bg-clip-text text-transparent">
                        واجباتي
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">
                        تابع مهامك الدراسية وقم بتسليم الواجبات في موعدها.
                    </p>
                </div>

                <!-- Filters -->
                <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                    <button wire:click="$set('filter', 'all')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filter === 'all' ? 'bg-white dark:bg-gray-700 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}">
                        الكل
                    </button>
                    <button wire:click="$set('filter', 'pending')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filter === 'pending' ? 'bg-white dark:bg-gray-700 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}">
                        قيد الانتظار
                    </button>
                    <button wire:click="$set('filter', 'submitted')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filter === 'submitted' ? 'bg-white dark:bg-gray-700 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}">
                        تم التسليم
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($this->submissions as $submission)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                        <!-- Status Badge -->
                        <!-- Status Badge -->
                        <div class="absolute top-4 left-4">
                            @php
                                $statusColors = [
                                    'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                                ];
                                $color = $submission->status->color();
                                $badgeClasses = $statusColors[$color] ?? $statusColors['gray'];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $badgeClasses }}">
                                {{ $submission->status->label() }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ $submission->homework->title }}</h3>
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $submission->homework->courseOffering->subject->name }}</p>
                        </div>

                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 line-clamp-2">{{ $submission->homework->description }}</p>

                        @if($submission->homework->attachment_path)
                            <a href="{{ Storage::url($submission->homework->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline mb-4 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-lg w-full justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                تحميل مرفق الواجب
                            </a>
                        @endif

                        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 border-t border-gray-100 dark:border-gray-700 pt-4">
                            <div class="flex justify-between">
                                <span class="text-gray-400">تاريخ الاستحقاق:</span>
                                <span class="font-medium {{ $submission->homework->due_date && now()->gt($submission->homework->due_date) && $submission->status === \App\Domains\Academic\Homework\Enums\SubmissionStatus::PENDING ? 'text-red-500' : '' }}">
                                    {{ $submission->homework->due_date ? $submission->homework->due_date->format('Y-m-d H:i') : 'غير محدد' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">نوع التسليم:</span>
                                <span>{{ $submission->homework->submission_type->label() }}</span>
                            </div>
                            @if($submission->score !== null)
                                <div class="flex justify-between bg-green-50 dark:bg-green-900/20 p-2 rounded-lg">
                                    <span class="text-green-700 dark:text-green-400 font-bold">الدرجة:</span>
                                    <span class="font-bold text-green-700 dark:text-green-400">{{ $submission->score }} / {{ $submission->homework->max_score }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="mt-6">
                            @if($submission->homework->submission_type === \App\Domains\Academic\Homework\Enums\SubmissionType::ONLINE)
                                @if($submission->status === \App\Domains\Academic\Homework\Enums\SubmissionStatus::PENDING || $submission->status === \App\Domains\Academic\Homework\Enums\SubmissionStatus::LATE)
                                    <button wire:click="openUploadModal({{ $submission->id }})" 
                                            class="w-full flex justify-center items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        رفع الملف
                                    </button>
                                @elseif($submission->file_path)
                                    <div class="text-center text-sm text-gray-500">
                                        تم رفع الملف في {{ $submission->submitted_at->format('Y-m-d H:i') }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center text-sm text-gray-500 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-xl">
                                    تسليم ورقي في الفصل
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">لا توجد واجبات</h3>
                        <p class="text-gray-500 dark:text-gray-400">أنت متفوق! لا توجد واجبات مطلوبة حالياً.</p>
                    </div>
                @endforelse
            </div>

            <!-- Upload Modal -->
            @if($showUploadModal)
                <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showUploadModal', false)"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                                    تسليم الواجب
                                </h3>
                                
                                <div class="space-y-4">
                                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center">
                                        <input type="file" wire:model="submissionFile" class="hidden" id="file-upload">
                                        <label for="file-upload" class="cursor-pointer">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                <span class="text-indigo-600 font-medium hover:text-indigo-500">اختر ملفاً</span> أو اسحبه هنا
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX, IMG up to 10MB</p>
                                        </label>
                                    </div>
                                    @if($submissionFile)
                                        <div class="text-sm text-green-600 font-medium">
                                            تم اختيار الملف: {{ $submissionFile->getClientOriginalName() }}
                                        </div>
                                    @endif
                                    @error('submissionFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" wire:click="uploadFile" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" wire:loading.attr="disabled">
                                    <span wire:loading.remove>رفع وتسليم</span>
                                    <span wire:loading>جاري الرفع...</span>
                                </button>
                                <button type="button" wire:click="$set('showUploadModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
