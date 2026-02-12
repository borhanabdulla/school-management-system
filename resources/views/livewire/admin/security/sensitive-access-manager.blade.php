<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" dir="rtl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">رمز الأمان الحساس</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">توليد رمز مؤقت للوصول إلى صفحات إغلاق/فتح السنة</p>
    </div>

    <div class="grid gap-6 max-w-3xl">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">الحالة الحالية</h2>

            @if ($hasActiveCode)
                <div class="flex items-center justify-between p-4 rounded-xl border {{ $activeExpired ? 'border-red-200 bg-red-50 text-red-700' : 'border-green-200 bg-green-50 text-green-700' }}">
                    <div>
                        <p class="font-semibold">{{ $activeExpired ? 'الرمز منتهي' : 'يوجد رمز فعال' }}</p>
                        @if ($activeExpiresAt)
                            <p class="text-sm mt-1">ينتهي في: {{ $activeExpiresAt }}</p>
                        @endif
                    </div>
                    <span class="text-sm">{{ $activeExpired ? 'غير صالح' : 'صالح' }}</span>
                </div>
            @else
                <div class="p-4 rounded-xl border border-yellow-200 bg-yellow-50 text-yellow-800">
                    لا يوجد رمز فعال حالياً.
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">توليد رمز جديد</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        مدة صلاحية الرمز (بالدقائق)
                    </label>
                    <input type="number" wire:model="expiresInMinutes" min="1" max="1440"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('expiresInMinutes')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">عند توليد رمز جديد يتم إلغاء أي رمز سابق تلقائياً.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button wire:click="generateCode"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        توليد الرمز
                    </button>
                    @if ($generatedCode)
                        <button wire:click="clearGeneratedCode"
                            class="px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            إخفاء الرمز
                        </button>
                    @endif
                </div>

                @if ($generatedCode)
                    <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-800">
                        <p class="text-sm mb-1">الرمز الجديد:</p>
                        <p class="text-2xl font-bold tracking-widest">{{ $generatedCode }}</p>
                        <p class="text-xs text-indigo-600 mt-2">احتفظ به في مكان آمن، وسيُطلب للدخول للصفحات الحساسة.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
