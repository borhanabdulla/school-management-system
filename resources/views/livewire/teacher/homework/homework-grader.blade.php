<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-l from-indigo-600 via-purple-600 to-teal-500 bg-clip-text text-transparent">
                        رصد درجات الواجب
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">
                        الواجب: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $this->homework->title }}</span>
                        (الدرجة العظمى: {{ $this->homework->max_score }})
                    </p>
                </div>

                <div class="flex gap-3">
                    <button wire:click="markAllFull" wire:confirm="هل أنت متأكد من منح الدرجة الكاملة لجميع الطلاب؟"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        رصد كامل للجميع
                    </button>
                    
                    <a href="{{ route('grading.homework.index', $this->homework->course_offering_id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-xl shadow transition-all">
                        عودة
                    </a>
                </div>
            </div>

            <!-- Grading Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">الطالب</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">الحالة</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">الملف (إن وجد)</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white w-32">الدرجة</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($this->homework->submissions as $submission)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                                                {{ substr($submission->student->first_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white">{{ $submission->student->full_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $submission->student->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $submission->status->color() }}-100 text-{{ $submission->status->color() }}-800">
                                            {{ $submission->status->label() }}
                                        </span>
                                        @if($submission->status === \App\Domains\Academic\Homework\Enums\SubmissionStatus::LATE)
                                            <span class="text-xs text-red-500 block mt-1">
                                                {{ $submission->submitted_at ? $submission->submitted_at->diffForHumans($this->homework->due_date) : '' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($submission->file_path)
                                            <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="text-indigo-600 hover:underline flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                تحميل
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" step="0.5" max="{{ $this->homework->max_score }}"
                                               wire:model.blur="grades.{{ $submission->id }}"
                                               wire:change="updateGrade({{ $submission->id }})"
                                               class="w-24 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-center font-bold">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" 
                                               wire:model.blur="feedbacks.{{ $submission->id }}"
                                               wire:change="updateGrade({{ $submission->id }})"
                                               placeholder="ملاحظات..."
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
