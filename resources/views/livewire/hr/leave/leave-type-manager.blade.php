<div dir="rtl">
    {{-- Page Header with Explanation --}}
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">أنواع الإجازات</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">إدارة أنواع الإجازات ورصيدها السنوي</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="recalculateBalances" wire:loading.attr="disabled" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" wire:loading.class="animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    إعادة حساب الأرصدة
                </button>
                <button wire:click="$set('showForm', true)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة نوع إجازة
                </button>
            </div>
        </div>

        {{-- Explanation Card --}}
        <div class="mt-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex gap-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-blue-800 dark:text-blue-300 text-sm mb-1">كيف تعمل أنواع الإجازات؟</h4>
                    <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                        <li>• كل نوع إجازة يُحدد <strong>الرصيد السنوي</strong> — عدد أيام الإجازة المسموحة لكل موظف سنوياً</li>
                        <li>• عند إنشاء نوع جديد، يتم تخصيص رصيد تلقائياً لجميع الموظفين النشطين</li>
                        <li>• يمكنك تحديد ما إذا كان النوع <strong>يتطلب إثبات</strong> (مثل تقرير طبي للإجازة المرضية)</li>
                        <li>• الموظف يستخدم هذه الأنواع عند تقديم <strong>طلب إجازة</strong> من لوحة التحكم الخاصة به</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    @if(isset($leaveTypes) && count($leaveTypes) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($leaveTypes) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">نوع إجازة</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ collect($leaveTypes)->where('is_active', true)->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">نوع نشط</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ collect($leaveTypes)->sum('annual_limit') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">إجمالي الأيام السنوية</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ collect($leaveTypes)->where('requires_proof', true)->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">يتطلب إثبات</p>
            </div>
        </div>
    @endif

    {{-- Leave Types Cards --}}
    @if(isset($leaveTypes) && count($leaveTypes) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($leaveTypes as $index => $type)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    {{-- Color Strip --}}
                    <div class="h-1.5 {{ $type['is_active'] ?? true ? 'bg-gradient-to-l from-indigo-500 to-purple-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                    
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $type['is_active'] ?? true ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-400' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $type['name'] }}</h3>
                                    @if(!($type['is_active'] ?? true))
                                        <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-1.5 py-0.5 rounded">غير نشط</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button wire:click="edit({{ $index }})" class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $index }})" wire:confirm="هل تريد حذف هذا النوع؟" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    الرصيد السنوي
                                </span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $type['annual_limit'] }} يوم</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    يتطلب إثبات
                                </span>
                                @if($type['requires_proof'] ?? false)
                                    <span class="text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-full">نعم</span>
                                @else
                                    <span class="text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded-full">لا</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">لم يتم إنشاء أنواع إجازات بعد</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 max-w-sm mx-auto">ابدأ بإضافة أنواع الإجازات المتاحة للموظفين مثل الإجازة السنوية، المرضية، والطارئة</p>
            <button wire:click="$set('showForm', true)" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                إضافة أول نوع إجازة
            </button>
        </div>
    @endif

    {{-- Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data x-transition>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md" @click.away="$wire.set('showForm', false)">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $editingIndex !== null ? 'تعديل نوع إجازة' : 'إضافة نوع إجازة جديد' }}
                    </h3>
                    <button wire:click="$set('showForm', false)" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اسم نوع الإجازة</label>
                        <input type="text" wire:model="form.name" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="مثال: إجازة سنوية">
                        @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            الرصيد السنوي (بالأيام)
                            <span class="text-gray-400 text-xs font-normal">— عدد أيام الإجازة المسموحة سنوياً</span>
                        </label>
                        <input type="number" wire:model="form.annual_limit" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0">
                        @error('form.annual_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="form.requires_proof" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                        <span class="text-sm text-gray-700 dark:text-gray-300">يتطلب إثبات (مثل تقرير طبي)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="form.is_active" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                        <span class="text-sm text-gray-700 dark:text-gray-300">نشط</span>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button wire:click="$set('showForm', false)" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        إلغاء
                    </button>
                    <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">
                        {{ $editingIndex !== null ? 'تحديث' : 'إضافة' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
