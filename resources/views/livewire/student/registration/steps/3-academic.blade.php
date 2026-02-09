<div class="space-y-8 animate-fade-in">
    <!-- Section Header -->
    <div class="flex items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-700/50">
        <div
            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                </path>
            </svg>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">البيانات الأكاديمية</h2>
            <p class="text-gray-500 dark:text-blue-200/70">تحديد الصف الدراسي والفصل للطالب</p>
        </div>
    </div>

    <!-- Enrollment Type & Transfer Info -->
    <div x-data="{ enrollmentType: @entangle('isTransferStudent') }" class="space-y-6">
        <div class="bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 shadow-sm dark:shadow-none">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-indigo-500/20 text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </span>
                نوع التسجيل
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Enrollment Type Toggle -->
                <div class="md:col-span-2">
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="isTransferStudent" :value="false"
                                class="peer sr-only">
                            <div
                                class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/20 peer-checked:border-indigo-500 peer-checked:border-indigo-500/50 transition-all text-center group hover:bg-gray-100 dark:hover:bg-white/10">
                                <div class="text-gray-900 dark:text-white font-bold mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-300">طالب مستجد</div>
                                <div class="text-xs text-gray-500 dark:text-white/50">تسجيل جديد في المدرسة</div>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="isTransferStudent" :value="true"
                                class="peer sr-only">
                            <div
                                class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/20 peer-checked:border-indigo-500 peer-checked:border-indigo-500/50 transition-all text-center group hover:bg-gray-100 dark:hover:bg-white/10">
                                <div class="text-gray-900 dark:text-white font-bold mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-300">منقول من مدرسة أخرى
                                </div>
                                <div class="text-xs text-gray-500 dark:text-white/50">لديه سجل أكاديمي سابق</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Transfer Details (Conditional) -->
                <div x-show="enrollmentType" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-white/10">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">اسم المدرسة السابقة</label>
                        <input type="text" wire:model="form.school_name"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        @error('form.school_name')
                            <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">المنهج الدراسي السابق</label>
                        <select wire:model="form.previous_curriculum"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all [&>option]:text-gray-900">
                            <option value="">اختر المنهج...</option>
                            <option value="ministry">وزاري (حكومي)</option>
                            <option value="american">دولي (أمريكي)</option>
                            <option value="british">دولي (بريطاني)</option>
                            <option value="other">آخر</option>
                        </select>
                        @error('form.previous_curriculum')
                            <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">آخر صف تم إتمامه</label>
                        <input type="text" wire:model="form.last_grade_completed"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        @error('form.last_grade_completed')
                            <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">سنة الإتمام</label>
                        <input type="number" wire:model="form.completion_year" placeholder="YYYY"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        @error('form.completion_year')
                            <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">سبب النقل</label>
                        <textarea wire:model="form.reason_for_transfer" rows="2"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all"></textarea>
                        @error('form.reason_for_transfer')
                            <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="relative group">
            <select wire:model.live="form.grade_id" id="grade_id"
                class="peer w-full px-5 py-4 bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm appearance-none">
                <option value="" class="bg-white dark:bg-gray-800">اختر الصف...</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" class="bg-white dark:bg-gray-800">{{ $grade->name }}</option>
                @endforeach
            </select>
            <label for="grade_id"
                class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-white dark:bg-gray-900/80 px-2 rounded-full transition-all peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 pointer-events-none">
                الصف الدراسي (Grade) <span class="text-red-400">*</span>
            </label>
            @error('form.grade_id')
                <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="relative group">
            <select wire:model="form.class_section_id" id="class_section_id"
                class="peer w-full px-5 py-4 bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm appearance-none disabled:opacity-50 disabled:cursor-not-allowed"
                {{ !$form->grade_id ? 'disabled' : '' }}>
                <option value="" class="bg-white dark:bg-gray-800">اختر الفصل (اختياري)...</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" class="bg-white dark:bg-gray-800">{{ $section->name }}
                        ({{ $section->gender_type }})
                    </option>
                @endforeach
            </select>
            <label for="class_section_id"
                class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-white dark:bg-gray-900/80 px-2 rounded-full transition-all peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 pointer-events-none">
                الفصل (Class Section)
            </label>
            <p class="text-xs text-gray-500 dark:text-white/40 mt-2 mr-2">يمكن ترك الفصل فارغاً وتسكين الطالب لاحقاً.</p>
        </div>
    </div>

    @if ($form->grade_id)
        <div
            class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-6 flex items-start gap-4 animate-fade-in-up backdrop-blur-sm">
            <div class="flex-shrink-0 mt-1">
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div>
                <h4 class="text-base font-bold text-emerald-900 dark:text-white">تأكيد التسجيل</h4>
                <p class="text-sm text-emerald-700 dark:text-emerald-200/80 mt-1 leading-relaxed">
                    سيتم تسجيل الطالب في السنة الأكاديمية الحالية النشطة. يرجى التأكد من اختيار الصف الصحيح بناءً على
                    عمر الطالب.
                </p>
            </div>
        </div>
    @endif
</div>
