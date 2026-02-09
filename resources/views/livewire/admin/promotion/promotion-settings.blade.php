<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" dir="rtl">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">إعدادات الترحيل</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">تخصيص قواعد الإكمال والترحيل</p>
    </div>

    <!-- Flash Messages -->
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
            <!-- قواعد الإكمال -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 pb-2 border-b dark:border-gray-700">
                    ⚖️ قواعد الإكمال (المُكمِّل)
                </h2>

                <div class="space-y-4">
                    <!-- عدد المواد -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            أقصى عدد مواد راسب فيها ليُعتبر مُكمِّل
                        </label>
                        <input type="number" wire:model="maxFailedForConditional" min="0" max="10"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <p class="text-sm text-gray-500 mt-1">
                            إذا رسب في عدد مواد أكبر من هذا الرقم يُعتبر راسباً
                        </p>
                    </div>

                    <!-- قرار المُكمِّل -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            قرار المُكمِّل
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" wire:model="conditionalDecision" value="promote" 
                                    class="ml-2 text-green-600">
                                <span class="text-gray-700 dark:text-gray-300">ينجح وينتقل</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model="conditionalDecision" value="repeat" 
                                    class="ml-2 text-red-600">
                                <span class="text-gray-700 dark:text-gray-300">يعيد السنة</span>
                            </label>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            ما يحدث للطالب المُكمِّل تلقائياً (يمكن للمدير تعديله)
                        </p>
                    </div>
                </div>
            </div>

            <!-- الربط المالي -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 pb-2 border-b dark:border-gray-700">
                    💰 الربط المالي
                </h2>

                <div class="space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" wire:model="requireFinancialClearanceForCertificate" 
                            class="rounded text-blue-600 w-5 h-5">
                        <div>
                            <span class="text-gray-900 dark:text-white font-medium">حجب الشهادة عند وجود مستحقات مالية</span>
                            <p class="text-sm text-gray-500">الطالب ينتقل للصف التالي لكن الشهادة تُحجب حتى السداد</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- الصلاحيات -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 pb-2 border-b dark:border-gray-700">
                    🔒 الصلاحيات
                </h2>

                <div class="space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" wire:model="directorOnlyCanOverride" 
                            class="rounded text-blue-600 w-5 h-5">
                        <div>
                            <span class="text-gray-900 dark:text-white font-medium">المدير فقط يستطيع تعديل النتائج</span>
                            <p class="text-sm text-gray-500">تعديل القرار من ناجح إلى راسب أو العكس</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button wire:click="saveSettings" 
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    💾 حفظ الإعدادات
                </button>
            </div>
        </div>
    </div>
</div>
