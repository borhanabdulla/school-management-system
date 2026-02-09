<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-black bg-gradient-to-l from-indigo-600 via-purple-600 to-pink-500 bg-clip-text text-transparent">
                إعدادات الحضور والغياب
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                تحكم في سياسة الحضور للسنة الدراسية الحالية. هذه الإعدادات ستنعكس على واجهات المعلمين والتقارير.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Settings Form -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. Attendance Mode -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            نمط الحضور (Attendance Mode)
                        </h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        @foreach($this->modes as $mode)
                            <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition-all hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $form->mode === $mode->value ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-gray-200 dark:border-gray-700' }}">
                                <div class="flex items-center h-5">
                                    <input type="radio" wire:model="form.mode" value="{{ $mode->value }}" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                </div>
                                <div class="mr-3 text-sm">
                                    <span class="font-bold text-gray-900 dark:text-white block">
                                        {{ $mode->label() }}
                                    </span>
                                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $mode->description() }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Responsibility & Tolerance -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </span>
                            المسؤولية والتأخير
                        </h2>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Responsible Role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المسؤول عن الرصد</label>
                            <select wire:model="form.responsible_role" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($this->roles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-gray-500">
                                يحدد من ستظهر له واجهة رصد الغياب في لوحة التحكم.
                            </p>
                        </div>

                        <!-- Late Tolerance -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">حد التسامح للتأخير (دقائق)</label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" wire:model="form.late_tolerance" min="0" max="60" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10">
                                <div class="absolute inset-y-0 right-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm px-3">دقيقة</span>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                أي تأخير أقل من هذا الرقم سيحسب كـ "حضور" ولن يظهر كتأخير.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button wire:click="save" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg transform transition hover:-translate-y-0.5">
                        <span wire:loading.remove wire:target="save">حفظ الإعدادات</span>
                        <span wire:loading wire:target="save">جاري الحفظ...</span>
                    </button>
                </div>

            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800">
                    <h3 class="text-lg font-bold text-indigo-800 dark:text-indigo-300 mb-3">كيف يعمل النظام؟</h3>
                    <ul class="space-y-3 text-sm text-indigo-700 dark:text-indigo-400">
                        <li class="flex gap-2">
                            <span class="font-bold">•</span>
                            <span>يتم تطبيق هذه الإعدادات على جميع المراحل الدراسية للسنة الحالية.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="font-bold">•</span>
                            <span>عند اختيار <strong>نقاط تفتيش</strong>، يجب عليك تحديد الحصص التي تعتبر نقاط تفتيش من خلال "إدارة قوالب الدوام".</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="font-bold">•</span>
                            <span>لا يمكن تغيير النمط بعد بدء رصد الغياب لتجنب تضارب البيانات.</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
        <!-- Confirmation Modal -->
        <div x-data="{ open: @entangle('showConfirmationModal') }" 
             x-show="open" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="open" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/50 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    تغيير نمط الحضور
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        لقد قمت بتغيير نمط الحضور وهناك سجلات حضور مسجلة بالفعل لهذه السنة. 
                                        تغيير النمط قد يؤدي إلى تضارب في التقارير والإحصائيات السابقة.
                                        <br><br>
                                        <strong>هل أنت متأكد أنك تريد المتابعة؟</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="confirmSave" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            نعم، قم بالتغيير
                        </button>
                        <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
