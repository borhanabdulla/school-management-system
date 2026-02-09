<div class="min-h-screen bg-gray-50/50 dark:bg-gray-900/50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 rtl:space-x-reverse md:space-x-3">
                    <li><a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 transition-colors">الرئيسية</a></li>
                    <li class="flex items-center">
                        <svg class="w-3 h-3 mx-2 text-gray-400 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                        <a href="{{ route('hr.staff.index') }}" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 transition-colors">الموظفون</a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-3 h-3 mx-2 text-gray-400 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                        <span class="text-gray-900 dark:text-white font-medium">إضافة موظف</span>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">إضافة موظف جديد</h1>
            <p class="mt-2 text-gray-500 dark:text-gray-400 text-lg">أدخل بيانات الموظف الجديد لإضافته إلى النظام وإنشاء ملفه الوظيفي.</p>
        </div>

        {{-- Alerts --}}
        <form wire:submit.prevent="save" class="space-y-8">
            
            {{-- Section 1: Personal Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">البيانات الشخصية</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">المعلومات الأساسية للموظف</p>
                    </div>
                </div>
                
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- First Name --}}
                    <div class="space-y-2">
                        <label for="first_name" class="block text-sm font-bold text-gray-700 dark:text-gray-300">الاسم الأول <span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" wire:model="first_name"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                            placeholder="مثال: أحمد">
                        @error('first_name') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Last Name --}}
                    <div class="space-y-2">
                        <label for="last_name" class="block text-sm font-bold text-gray-700 dark:text-gray-300">الاسم الأخير <span class="text-red-500">*</span></label>
                        <input type="text" id="last_name" wire:model="last_name"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                            placeholder="مثال: محمد">
                        @error('last_name') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-bold text-gray-700 dark:text-gray-300">البريد الإلكتروني <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="email" id="email" wire:model.blur="email" dir="ltr"
                                class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400 text-left"
                                placeholder="name@school.com">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                        @error('email') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="space-y-2">
                        <label for="phone" class="block text-sm font-bold text-gray-700 dark:text-gray-300">رقم الهاتف</label>
                        <div class="relative">
                            <input type="text" id="phone" wire:model="phone" dir="ltr"
                                class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400 text-left"
                                placeholder="05xxxxxxxx">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                        </div>
                        @error('phone') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Section 2: Job Details --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">بيانات الوظيفة</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">الدور الوظيفي وتفاصيل العقد</p>
                    </div>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Role --}}
                    <div class="space-y-2">
                        <label for="role" class="block text-sm font-bold text-gray-700 dark:text-gray-300">الدور الوظيفي <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select id="role" wire:model.live="role"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="">اختر الدور...</option>
                                @foreach ($this->roles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('role') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Job Title --}}
                    <div class="space-y-2">
                        <label for="job_title" class="block text-sm font-bold text-gray-700 dark:text-gray-300">المسمى الوظيفي <span class="text-red-500">*</span></label>
                        <input type="text" id="job_title" wire:model="job_title"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                            placeholder="مثال: معلم رياضيات">
                        @error('job_title') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Work Shift --}}
                    <div class="space-y-2">
                        <label for="work_shift_id" class="block text-sm font-bold text-gray-700 dark:text-gray-300">فترة الدوام</label>
                        <div class="relative">
                            <select id="work_shift_id" wire:model="work_shift_id"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="">بدون فترة محددة</option>
                                @foreach ($workShifts as $shift)
                                    <option value="{{ $shift['id'] }}">{{ $shift['name'] }} ({{ $shift['time'] }})</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Employment Type --}}
                    <div class="space-y-2">
                        <label for="employment_type" class="block text-sm font-bold text-gray-700 dark:text-gray-300">نوع التوظيف <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select id="employment_type" wire:model="employment_type"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="full_time">دوام كامل</option>
                                <option value="part_time">دوام جزئي</option>
                                <option value="contractor">مقاول / متعاقد</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Joining Date --}}
                    <div class="space-y-2">
                        <label for="joining_date" class="block text-sm font-bold text-gray-700 dark:text-gray-300">تاريخ الالتحاق <span class="text-red-500">*</span></label>
                        <input type="date" id="joining_date" wire:model="joining_date"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        @error('joining_date') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Create Account Checkbox --}}
                    <div class="md:col-span-2 pt-4">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="checkbox" wire:model="create_account" class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">إنشاء حساب دخول للنظام تلقائياً</span>
                        </label>
                        <p class="mt-1 mr-17 text-xs text-gray-500 dark:text-gray-400">سيتم إرسال بيانات الدخول إلى البريد الإلكتروني المسجل.</p>
                    </div>
                </div>
            </div>

            {{-- Section 3: Teacher Details (Conditional) --}}
            @if ($this->showTeacherFields)
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-in-up"
                     x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'center' })">
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-purple-50/50 dark:bg-purple-900/20 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">بيانات المعلم</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">معلومات إضافية خاصة بالهيئة التعليمية</p>
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Specialization --}}
                        <div class="space-y-2">
                            <label for="specialization" class="block text-sm font-bold text-gray-700 dark:text-gray-300">التخصص <span class="text-red-500">*</span></label>
                            <input type="text" id="specialization" wire:model="specialization"
                                list="specializations-list"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all placeholder-gray-400"
                                placeholder="مثال: رياضيات">
                            <datalist id="specializations-list">
                                @foreach ($existingSpecializations as $spec)
                                    <option value="{{ $spec }}">
                                @endforeach
                            </datalist>
                            @error('specialization') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Max Load --}}
                        <div class="space-y-2">
                            <label for="max_weekly_classes" class="block text-sm font-bold text-gray-700 dark:text-gray-300">الحد الأقصى للحصص الأسبوعية <span class="text-red-500">*</span></label>
                            <input type="number" id="max_weekly_classes" wire:model="max_weekly_classes"
                                min="1" max="40"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all placeholder-gray-400">
                            @error('max_weekly_classes') <p class="text-sm text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4">
                <a href="{{ route('hr.staff.index') }}"
                    class="px-8 py-3.5 text-center rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all font-bold">
                    إلغاء
                </a>
                
                <button type="button" wire:click="saveAndCreateAnother"
                    class="px-8 py-3.5 rounded-xl border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all font-bold flex items-center justify-center"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveAndCreateAnother">حفظ وإضافة آخر</span>
                    <span wire:loading wire:target="saveAndCreateAnother" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        جاري الحفظ...
                    </span>
                </button>

                <button type="submit"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white transition-all font-bold shadow-lg shadow-indigo-500/30 flex items-center justify-center"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">
                        <svg class="w-5 h-5 ml-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        حفظ الموظف
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        جاري الحفظ...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
