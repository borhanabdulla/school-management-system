<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" dir="rtl">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">تأكيد الدخول</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">هذه الصفحة محمية برمز أمان إضافي.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
            @if (!$hasActiveCode)
                <div class="p-4 rounded-xl border border-yellow-200 bg-yellow-50 text-yellow-800 mb-4">
                    لا يوجد رمز فعال حالياً. تواصل مع مدير النظام لتوليد رمز جديد.
                </div>
            @elseif ($activeExpired)
                <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 mb-4">
                    الرمز الحالي منتهي الصلاحية. يرجى توليد رمز جديد.
                </div>
            @endif

            @if ($activeExpiresAt)
                <p class="text-sm text-gray-500 mb-4">انتهاء الصلاحية: {{ $activeExpiresAt }}</p>
            @endif

            <form wire:submit.prevent="verify" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">رمز الأمان</label>
                    <input type="text" wire:model="code" maxlength="6" inputmode="numeric"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="ادخل الرمز">
                    @error('code')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    تأكيد
                </button>
            </form>

            @can('sensitive.manage')
                <div class="mt-4 text-center">
                    <a href="{{ route('security.sensitive-access') }}" class="text-sm text-blue-600 hover:underline">
                        توليد رمز جديد
                    </a>
                </div>
            @endcan
        </div>
    </div>
</div>
