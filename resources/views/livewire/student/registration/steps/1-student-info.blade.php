<div class="space-y-8 animate-fade-in">
    <!-- Section Header -->
    <div class="flex items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-700/50">
        <div
            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">البيانات الشخصية للطالب</h2>
            <p class="text-gray-500 dark:text-blue-200/70">يرجى إدخال البيانات الأساسية للطالب بدقة</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Arabic Name -->
        <div class="space-y-6">
            <div class="relative group">
                <input wire:model="form.first_name_ar" type="text" id="first_name_ar"
                    class="peer w-full px-5 py-4 bg-background dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-foreground dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm"
                    placeholder=" ">
                <label for="first_name_ar"
                    class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-background dark:bg-gray-900/80 px-2 rounded-full transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 dark:peer-placeholder-shown:text-white/50 peer-focus:-top-3 peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 peer-focus:text-xs peer-focus:bg-background dark:peer-focus:bg-gray-900/80 peer-focus:px-2 peer-focus:rounded-full pointer-events-none">
                    الاسم الأول (بالعربية) <span class="text-red-500 dark:text-red-400">*</span>
                </label>
                @error('form.first_name_ar')
                    <span class="text-red-500 dark:text-red-400 text-xs mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="relative group">
                <input wire:model="form.family_name_ar" type="text" id="family_name_ar"
                    class="peer w-full px-5 py-4 bg-background dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-foreground dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm"
                    placeholder=" ">
                <label for="family_name_ar"
                    class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-background dark:bg-gray-900/80 px-2 rounded-full transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 dark:peer-placeholder-shown:text-white/50 peer-focus:-top-3 peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 peer-focus:text-xs peer-focus:bg-background dark:peer-focus:bg-gray-900/80 peer-focus:px-2 peer-focus:rounded-full pointer-events-none">
                    اسم العائلة (بالعربية) <span class="text-red-500 dark:text-red-400">*</span>
                </label>
                @error('form.family_name_ar')
                    <span class="text-red-500 dark:text-red-400 text-xs mt-2 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Personal Details -->
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div class="relative group">
                    <input wire:model="form.date_of_birth" type="date" id="date_of_birth"
                        class="peer w-full px-5 py-4 bg-background dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-foreground dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm">
                    <label for="date_of_birth"
                        class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-background dark:bg-gray-900/80 px-2 rounded-full transition-all peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 pointer-events-none">
                        تاريخ الميلاد <span class="text-red-500 dark:text-red-400">*</span>
                    </label>
                    @error('form.date_of_birth')
                        <span class="text-red-500 dark:text-red-400 text-xs mt-2 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="relative group">
                    <select wire:model="form.gender" id="gender"
                        class="peer w-full px-5 py-4 bg-background dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-foreground dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm appearance-none">
                        <option value="male" class="bg-background dark:bg-gray-800">ذكر</option>
                        <option value="female" class="bg-background dark:bg-gray-800">أنثى</option>
                    </select>
                    <label for="gender"
                        class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-background dark:bg-gray-900/80 px-2 rounded-full transition-all peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 pointer-events-none">
                        الجنس <span class="text-red-500 dark:text-red-400">*</span>
                    </label>
                </div>
            </div>

            <div class="relative group">
                <input wire:model.blur="form.national_id" type="text" id="national_id"
                    class="peer w-full px-5 py-4 bg-background dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-foreground dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm"
                    placeholder=" ">
                <label for="national_id"
                    class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-background dark:bg-gray-900/80 px-2 rounded-full transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 dark:peer-placeholder-shown:text-white/50 peer-focus:-top-3 peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 peer-focus:text-xs peer-focus:bg-background dark:peer-focus:bg-gray-900/80 peer-focus:px-2 peer-focus:rounded-full pointer-events-none">
                    الرقم القومي / الإقامة
                </label>
                @error('form.national_id')
                    <span class="text-red-500 dark:text-red-400 text-xs mt-2 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Nationality & Blood Type -->
        <div class="space-y-6">
            <x-form.searchable-select name="nationality_id" label="الجنسية" :options="$countries"
                wire:model="form.nationality_id" option-label="name_ar" option-value="id"
                placeholder="ابحث عن الدولة..." :selected="$form->nationality_id ? $countries->firstWhere('id', $form->nationality_id) : null" />
        </div>

        <div class="space-y-6">
            <div class="relative group">
                <select wire:model="form.blood_type" id="blood_type"
                    class="peer w-full px-5 py-4 bg-background dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl text-foreground dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all backdrop-blur-sm appearance-none">
                    <option value="" class="bg-background dark:bg-gray-800">غير محدد</option>
                    <option value="A+" class="bg-background dark:bg-gray-800">A+</option>
                    <option value="A-" class="bg-background dark:bg-gray-800">A-</option>
                    <option value="B+" class="bg-background dark:bg-gray-800">B+</option>
                    <option value="B-" class="bg-background dark:bg-gray-800">B-</option>
                    <option value="O+" class="bg-background dark:bg-gray-800">O+</option>
                    <option value="O-" class="bg-background dark:bg-gray-800">O-</option>
                    <option value="AB+" class="bg-background dark:bg-gray-800">AB+</option>
                    <option value="AB-" class="bg-background dark:bg-gray-800">AB-</option>
                </select>
                <label for="blood_type"
                    class="absolute right-5 -top-3 text-xs text-gray-500 dark:text-white/50 bg-background dark:bg-gray-900/80 px-2 rounded-full transition-all peer-focus:text-emerald-600 dark:peer-focus:text-emerald-400 pointer-events-none">
                    فصيلة الدم
                </label>
            </div>
        </div>

        <div class="space-y-6">
            <div class="relative group">
                <label for="student_photo" class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-3">
                    صورة الطالب (اختياري)
                </label>
                <input wire:model="form.photo" type="file" id="student_photo" accept="image/png,image/jpeg"
                    class="block w-full text-sm text-gray-700 dark:text-white file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-500/10 file:text-emerald-700 dark:file:text-emerald-300 hover:file:bg-emerald-500/20 transition-all border border-gray-200 dark:border-gray-700 rounded-2xl bg-background dark:bg-gray-900/50">
                @error('form.photo')
                    <span class="text-red-500 dark:text-red-400 text-xs mt-2 block">{{ $message }}</span>
                @enderror

                @if ($form->photo)
                    <div class="mt-4">
                        <img src="{{ $form->photo->temporaryUrl() }}" alt="معاينة الصورة"
                            class="w-28 h-28 rounded-2xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm">
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Health Conditions Section -->
    <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700/50">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center text-red-500 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">الحالة الصحية (اختياري)</h3>
            </div>
            <button wire:click="addHealthCondition"
                class="px-4 py-2 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-900 dark:text-white rounded-xl transition-all flex items-center gap-2 text-sm font-medium backdrop-blur-sm border border-gray-200 dark:border-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                إضافة حالة
            </button>
        </div>

        @if (count($healthConditions) > 0)
            <div class="space-y-4">
                @foreach ($healthConditions as $index => $condition)
                    <div
                        class="flex flex-col md:flex-row gap-4 items-start bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-200 dark:border-gray-700/50 transition-all hover:border-gray-300 dark:hover:border-white/20">
                        <div class="flex-1 w-full">
                            <x-form.searchable-select name="healthConditions.{{ $index }}.condition_id"
                                label="نوع الحالة" :options="$healthTypes"
                                wire:model="healthConditions.{{ $index }}.condition_id" option-label="name"
                                option-value="id" placeholder="ابحث عن الحالة..." :selected="isset($healthConditions[$index]['condition_id']) &&
                                $healthConditions[$index]['condition_id']
                                    ? $healthTypes->firstWhere('id', $healthConditions[$index]['condition_id'])
                                    : null" />
                        </div>
                        <div class="flex-[2] w-full relative group">
                            <input wire:model="healthConditions.{{ $index }}.notes" type="text"
                                class="peer w-full px-4 py-3 bg-background dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl text-foreground dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 transition-all"
                                placeholder=" ">
                            <label
                                class="absolute right-4 top-3.5 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-2.5 peer-focus:text-xs peer-focus:bg-background dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-base {{ $healthConditions[$index]['notes'] ? '-top-2.5 text-xs bg-background dark:bg-gray-900 px-2 rounded-full' : '' }}">ملاحظات
                                إضافية</label>
                        </div>
                        <button wire:click="removeHealthCondition({{ $index }})"
                            class="mt-2 text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/20 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="text-center py-8 bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-white/40">لا توجد حالات صحية مسجلة. اضغط على "إضافة حالة" إذا كان الطالب يعاني
                    من أي مشاكل صحية.</p>
            </div>
        @endif
    </div>
</div>
