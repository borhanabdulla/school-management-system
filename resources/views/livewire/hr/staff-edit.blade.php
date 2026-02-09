<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 rtl:space-x-reverse md:space-x-3">
                    <li><a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">الرئيسية</a></li>
                    <li class="flex items-center">
                        <svg class="w-3 h-3 mx-2 text-gray-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <a href="{{ route('hr.staff.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">الموظفون</a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-3 h-3 mx-2 text-gray-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <span class="text-gray-700 dark:text-gray-200">تعديل: {{ $staff->full_name }}</span>
                    </li>
                </ol>
            </nav>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">تعديل بيانات الموظف</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">الرقم الوظيفي: {{ $staff->employee_number }}</p>
                </div>
                <button wire:click="checkDelete" type="button"
                    class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    حذف
                </button>
            </div>
        </div>

        {{-- Flash Messages --}}
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                {{-- Card 1: البيانات الشخصية --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-750">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 ml-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            البيانات الشخصية
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- الاسم الأول --}}
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    الاسم الأول <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="first_name" wire:model="first_name"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('first_name') border-red-500 @enderror">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- الاسم الأخير --}}
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    الاسم الأخير <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="last_name" wire:model="last_name"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('last_name') border-red-500 @enderror">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- الهاتف --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    رقم الهاتف
                                </label>
                                <input type="text" id="phone" wire:model="phone"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    dir="ltr">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- الحالة --}}
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    الحالة <span class="text-red-500">*</span>
                                </label>
                                <select id="status" wire:model="status"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    <option value="active">نشط</option>
                                    <option value="on_leave">في إجازة</option>
                                    <option value="terminated">منتهي</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: بيانات الوظيفة --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-gray-800 dark:to-gray-750">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 ml-2 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            بيانات الوظيفة
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- الدور الوظيفي --}}
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    الدور الوظيفي <span class="text-red-500">*</span>
                                </label>
                                <select id="role" wire:model.live="role"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('role') border-red-500 @enderror">
                                    <option value="">اختر الدور...</option>
                                    @foreach ($this->roles as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- المسمى الوظيفي --}}
                            <div>
                                <label for="job_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    المسمى الوظيفي <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="job_title" wire:model="job_title"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('job_title') border-red-500 @enderror">
                                @error('job_title')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- فترة الدوام --}}
                            <div>
                                <label for="work_shift_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    فترة الدوام
                                </label>
                                <select id="work_shift_id" wire:model="work_shift_id"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    <option value="">بدون فترة محددة</option>
                                    @foreach ($workShifts as $shift)
                                        <option value="{{ $shift['id'] }}">{{ $shift['name'] }} ({{ $shift['time'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- نوع التوظيف --}}
                            <div>
                                <label for="employment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    نوع التوظيف <span class="text-red-500">*</span>
                                </label>
                                <select id="employment_type" wire:model="employment_type"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    <option value="full_time">دوام كامل</option>
                                    <option value="part_time">دوام جزئي</option>
                                    <option value="contractor">مقاول / متعاقد</option>
                                </select>
                            </div>

                            {{-- تاريخ الالتحاق --}}
                            <div>
                                <label for="joining_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    تاريخ الالتحاق <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="joining_date" wire:model="joining_date"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('joining_date') border-red-500 @enderror">
                                @error('joining_date')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: بيانات المعلم --}}
                @if ($this->showTeacherFields)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-800 dark:to-gray-750">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 ml-2 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                بيانات المعلم
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- التخصص --}}
                                <div>
                                    <label for="specialization" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        التخصص
                                    </label>
                                    <input type="text" id="specialization" wire:model="specialization"
                                        list="specializations-list"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    <datalist id="specializations-list">
                                        @foreach ($existingSpecializations as $spec)
                                            <option value="{{ $spec }}">
                                        @endforeach
                                    </datalist>
                                </div>

                                {{-- الحد الأقصى للحصص --}}
                                <div>
                                    <label for="max_weekly_classes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        الحد الأقصى للحصص الأسبوعية
                                    </label>
                                    <input type="number" id="max_weekly_classes" wire:model="max_weekly_classes"
                                        min="1" max="40"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6">
                    <a href="{{ route('hr.staff.index') }}"
                        class="px-6 py-2.5 text-center rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all font-medium">
                        إلغاء
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white transition-all font-medium shadow-lg shadow-blue-500/25 flex items-center justify-center"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <svg class="w-5 h-5 ml-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            حفظ التعديلات
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            جاري الحفظ...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" wire:click="$set('showDeleteModal', false)"></div>

                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-right align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>

                    <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white text-center">
                        حذف الموظف: {{ $staff->full_name }}
                    </h3>

                    @if (!$deleteCheckResult['can_delete'])
                        <div class="p-4 mb-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800">
                            <p class="font-medium text-yellow-800 dark:text-yellow-300 mb-2">⚠️ تحذير: هناك بيانات مرتبطة</p>
                            <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-400">
                                @foreach ($deleteCheckResult['reasons'] as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                        @if ($deleteCheckResult['can_delete'])
                            هل أنت متأكد من حذف هذا الموظف؟ لا يمكن التراجع عن هذا الإجراء.
                        @else
                            يمكنك تعطيل الموظف بدلاً من الحذف للحفاظ على البيانات.
                        @endif
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            إلغاء
                        </button>
                        <button type="button" wire:click="deactivate"
                            class="px-4 py-2 text-sm font-medium text-yellow-700 bg-yellow-100 rounded-lg hover:bg-yellow-200 dark:text-yellow-300 dark:bg-yellow-900/30 dark:hover:bg-yellow-900/50">
                            تعطيل فقط
                        </button>
                        <button type="button" wire:click="delete"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            حذف نهائي
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
