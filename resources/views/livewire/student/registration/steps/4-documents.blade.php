<div class="space-y-8 animate-fade-in">
    <!-- Section Header -->
    <div class="flex items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-700/50">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">المستندات والعنوان</h2>
            <p class="text-gray-500 dark:text-blue-200/70">إرفاق المستندات المطلوبة وتحديد عنوان السكن</p>
        </div>
    </div>

    <!-- Address Section -->
    <div class="bg-white dark:bg-gray-800/40 p-8 rounded-3xl border border-gray-200 dark:border-gray-700/50 relative overflow-hidden backdrop-blur-sm shadow-sm dark:shadow-none">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3">
            <span class="w-1.5 h-8 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></span>
            بيانات العنوان
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="relative group">
                <input wire:model="form.city" type="text" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">المدينة <span class="text-red-500 dark:text-red-400">*</span></label>
                @error('form.city') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
            </div>
            <div class="relative group">
                <input wire:model="form.district" type="text" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">الحي <span class="text-red-500 dark:text-red-400">*</span></label>
                @error('form.district') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
            </div>
            <div class="relative group">
                <input wire:model="form.street_name" type="text" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">اسم الشارع <span class="text-red-500 dark:text-red-400">*</span></label>
                @error('form.street_name') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
            </div>
            <div class="relative group">
                <input wire:model="form.building_number" type="text" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">رقم المبنى (اختياري)</label>
            </div>
            <div class="md:col-span-2 relative group">
                <input wire:model="form.google_maps_link" type="text" class="peer w-full pl-5 pr-12 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                <label class="absolute right-12 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">رابط Google Maps (اختياري)</label>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-gray-400 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                @error('form.google_maps_link') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <!-- Documents Upload Section -->
    <div class="bg-white dark:bg-gray-800/40 p-8 rounded-3xl border border-gray-200 dark:border-gray-700/50 relative overflow-hidden backdrop-blur-sm shadow-sm dark:shadow-none">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span class="w-1.5 h-8 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></span>
                المستندات المطلوبة
            </h3>
            <button wire:click="addDocument" type="button"
                class="px-4 py-2 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-900 dark:text-white rounded-xl transition-all flex items-center gap-2 text-sm font-medium backdrop-blur-sm border border-gray-200 dark:border-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                إضافة مستند
            </button>
        </div>

        <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-6 mb-8 flex gap-4 items-start">
            <div class="flex-shrink-0 p-2 bg-blue-500/20 rounded-lg">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-sm text-blue-200/80 leading-relaxed pt-1">
                يمكنك رفع المستندات الآن أو تركها فارغة والرفع لاحقاً. الصيغ المدعومة: PDF, JPG, PNG. الحد الأقصى: 10 ميجابايت.
            </p>
        </div>

        @error('documents')
            <div class="p-4 mb-6 bg-red-500/10 border border-red-500/20 text-red-300 rounded-xl flex items-center gap-3 animate-pulse">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold">{{ $message }}</span>
            </div>
        @enderror

        @if (count($documents) > 0)
            <div class="space-y-4">
                @foreach ($documents as $index => $document)
                    <div class="flex flex-col md:flex-row gap-6 items-start bg-gray-50 dark:bg-black/20 p-6 rounded-2xl border border-gray-200 dark:border-white/5 hover:border-gray-300 dark:hover:border-white/20 transition-all group">
                        <div class="flex-1 w-full relative">
                            <select wire:model="documents.{{ $index }}.type"
                                class="w-full px-4 py-3 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all appearance-none">
                                <option value="" class="bg-white dark:bg-gray-800">اختر النوع...</option>
                                <option value="birth_certificate" class="bg-white dark:bg-gray-800">شهادة ميلاد</option>
                                <option value="national_id" class="bg-white dark:bg-gray-800">بطاقة هوية</option>
                                <option value="passport" class="bg-white dark:bg-gray-800">جواز سفر</option>
                                <option value="last_certificate" class="bg-white dark:bg-gray-800">آخر شهادة دراسية</option>
                                <option value="medical_report" class="bg-white dark:bg-gray-800">تقرير طبي</option>
                                <option value="other" class="bg-white dark:bg-gray-800">أخرى</option>
                            </select>
                            <label class="absolute right-4 -top-2.5 text-xs text-gray-500 dark:text-white/50 bg-white dark:bg-gray-900 px-2 rounded-full">نوع المستند</label>
                        </div>
                        <div class="flex-[2] w-full relative">
                            <label class="block w-full cursor-pointer group-hover:border-blue-500/50 transition-colors">
                                <input type="file" wire:model="documents.{{ $index }}.file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 dark:text-white/60
                                    file:mr-4 file:py-2.5 file:px-6
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-bold
                                    file:bg-blue-500/20 file:text-blue-600 dark:file:text-blue-300
                                    hover:file:bg-blue-500/30
                                    cursor-pointer border border-gray-200 dark:border-white/10 rounded-xl p-2 bg-white dark:bg-white/5
                                "/>
                            </label>
                            <label class="absolute right-4 -top-2.5 text-xs text-gray-500 dark:text-white/50 bg-white dark:bg-gray-900 px-2 rounded-full">ملف المستند</label>
                        </div>
                        @if ($index > 0)
                            <button wire:click="removeDocument({{ $index }})" type="button"
                                class="mt-2 text-gray-400 dark:text-white/30 hover:text-red-500 dark:hover:text-red-400 p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-gray-50 dark:bg-white/5 rounded-3xl border-2 border-dashed border-gray-200 dark:border-white/10 group hover:border-gray-300 dark:hover:border-white/20 transition-colors">
                <div class="w-16 h-16 bg-white dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 dark:text-white/20 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-white/40 font-medium">لم يتم إضافة مستندات بعد</p>
                <button wire:click="addDocument" class="mt-4 text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 text-sm font-bold hover:underline">إضافة مستند جديد</button>
            </div>
        @endif
    </div>

    <!-- Previous School History Section -->
    <div class="bg-white dark:bg-gray-800/40 p-8 rounded-3xl border border-gray-200 dark:border-gray-700/50 relative overflow-hidden backdrop-blur-sm shadow-sm dark:shadow-none">
        <div class="flex items-center gap-4 mb-8">
            <label class="relative inline-flex items-center cursor-pointer group">
                <input type="checkbox" wire:model.live="isTransferStudent" class="sr-only peer">
                <div class="w-14 h-7 bg-gray-200 dark:bg-black/40 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                <span class="mr-4 text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-200 transition-colors">الطالب منقول من مدرسة أخرى</span>
            </label>
        </div>

        @if ($isTransferStudent)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-down">
                <div class="md:col-span-2 relative group">
                    <input wire:model="form.school_name" type="text" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                    <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">اسم المدرسة السابقة</label>
                    @error('form.school_name') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
                </div>
                <div class="relative group">
                    <select wire:model="form.previous_curriculum" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all appearance-none">
                        <option value="" class="bg-white dark:bg-gray-800">اختر المنهج...</option>
                        <option value="وزاري" class="bg-white dark:bg-gray-800">وزاري (حكومي)</option>
                        <option value="أمريكي" class="bg-white dark:bg-gray-800">أمريكي</option>
                        <option value="بريطاني" class="bg-white dark:bg-gray-800">بريطاني</option>
                        <option value="IB" class="bg-white dark:bg-gray-800">IB (البكالوريا الدولية)</option>
                        <option value="أخرى" class="bg-white dark:bg-gray-800">أخرى</option>
                    </select>
                    <label class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-white dark:bg-gray-900 px-2 rounded-full">المنهج السابق</label>
                    @error('form.previous_curriculum') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
                </div>
                <div class="relative group">
                    <input wire:model="form.last_grade_completed" type="text" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                    <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">آخر صف أنهاه</label>
                    @error('form.last_grade_completed') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
                </div>
                <div class="relative group">
                    <input wire:model="form.completion_year" type="number" min="2000" max="{{ date('Y') }}" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                    <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">سنة الإنهاء</label>
                    @error('form.completion_year') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
                </div>
                <div class="relative group">
                    <input wire:model="form.last_gpa" type="number" step="0.01" min="0" max="100" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" ">
                    <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">المعدل النهائي (اختياري)</label>
                </div>
                <div class="md:col-span-2 relative group">
                    <textarea wire:model="form.reason_for_transfer" rows="3" class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder=" "></textarea>
                    <label class="absolute right-5 top-4 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">سبب النقل</label>
                </div>
            </div>
        @endif
    </div>
</div>
