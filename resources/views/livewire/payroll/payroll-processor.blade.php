<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full {{ $step >= 1 ? 'text-indigo-600 bg-indigo-200' : 'text-gray-600 bg-gray-200' }}">
                    الإعدادات
                </span>
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full {{ $step >= 2 ? 'text-indigo-600 bg-indigo-200' : 'text-gray-600 bg-gray-200' }}">
                    التحقق
                </span>
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full {{ $step >= 3 ? 'text-indigo-600 bg-indigo-200' : 'text-gray-600 bg-gray-200' }}">
                    المعالجة
                </span>
            </div>
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                <div style="width: {{ ($step / 3) * 100 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-500"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            
            {{-- Step 1: Configuration --}}
            @if($step === 1)
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">إعداد دفعة الرواتب</h2>
                    
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">السنة</label>
                                <select wire:model.live="year" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    @for($y = 2024; $y <= 2030; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الشهر</label>
                                <select wire:model.live="month" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">اسم الدفعة</label>
                            <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ملاحظات</label>
                            <textarea wire:model="notes" rows="3" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>

                        @if($errors->any())
                            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button wire:click="validateConfig" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                            التالي: التحقق
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 2: Validation (Fail-Safe) --}}
            @if($step === 2)
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">التحقق من البيانات (Fail-Safe)</h2>

                    <div class="space-y-4 mb-6">
                        @if(count($validationErrors) > 0)
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <h3 class="font-bold text-red-800 dark:text-red-300">تم اكتشاف مشاكل تمنع التوليد</h3>
                                </div>
                                <ul class="list-disc list-inside text-red-700 dark:text-red-400 space-y-1">
                                    @foreach($validationErrors as $error)
                                        <li>{{ $error['message'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <h3 class="font-bold text-green-800 dark:text-green-300">البيانات جاهزة للمعالجة</h3>
                                        <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                            سيتم معالجة الرواتب لـ <strong>{{ $stats['contracts_count'] ?? 0 }}</strong> عقد نشط.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between">
                        <button wire:click="back" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            عودة
                        </button>
                        @if(count($validationErrors) === 0)
                            <button wire:click="generate" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-2">
                                <span wire:loading.remove wire:target="generate">توليد الدفعة</span>
                                <span wire:loading wire:target="generate">جاري المعالجة...</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Step 3: Processing (Loading State handled by wire:loading above, but explicit step for logic) --}}
            @if($step === 3)
                <div class="p-12 text-center">
                    <svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">جاري معالجة الرواتب...</h3>
                    <p class="text-gray-500 mt-2">يرجى الانتظار، قد تستغرق العملية بضع لحظات.</p>
                </div>
            @endif

        </div>
    </div>
</div>
