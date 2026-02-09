<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">إعدادات سياسات الرواتب</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">تكوين قواعد احتساب الرواتب والخصومات</p>
        </div>

        {{-- Alerts --}}
        <form wire:submit="save" class="space-y-8">
            {{-- Day Calculation Method --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">طريقة احتساب الأيام</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">تحديد كيف يتم حساب قيمة اليوم الواحد من الراتب</p>
                
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <input type="radio" wire:model="dayCalculationMethod" value="calendar_30" class="text-indigo-600">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">30 يوم ثابت</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">الراتب ÷ 30 (الطريقة الأكثر شيوعاً)</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <input type="radio" wire:model="dayCalculationMethod" value="working_26" class="text-indigo-600">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">26 يوم عمل</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">الراتب ÷ 26 (استثناء الجمعة)</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <input type="radio" wire:model="dayCalculationMethod" value="actual_month" class="text-indigo-600">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">أيام الشهر الفعلية</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">الراتب ÷ عدد أيام الشهر الفعلي</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Lateness Policy --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">سياسة التأخير</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">تحديد شرائح خصم التأخير</p>
                    </div>
                    <button type="button" wire:click="addThreshold" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-sm font-medium hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition-colors">
                        + إضافة شريحة
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($latenessThresholds as $index => $threshold)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="text-sm text-gray-500 dark:text-gray-400">من</span>
                                <input type="number" wire:model="latenessThresholds.{{ $index }}.min" class="w-16 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" min="0">
                                <span class="text-sm text-gray-500 dark:text-gray-400">إلى</span>
                                <input type="number" wire:model="latenessThresholds.{{ $index }}.max" class="w-16 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" min="0">
                                <span class="text-sm text-gray-500 dark:text-gray-400">دقيقة</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">الخصم:</span>
                                <input type="text" wire:model="latenessThresholds.{{ $index }}.deduction_minutes" class="w-24 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" placeholder="دقائق أو half_day">
                            </div>
                            <button type="button" wire:click="removeThreshold({{ $index }})" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Absence Policy --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">سياسة الغياب</h2>
                <div class="flex items-center gap-4">
                    <label class="text-gray-600 dark:text-gray-300">معامل الخصم لليوم الغياب:</label>
                    <input type="number" step="0.1" wire:model="absenceMultiplier" class="w-24 px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                    <span class="text-gray-500 dark:text-gray-400 text-sm">× قيمة اليوم</span>
                </div>
                <p class="text-gray-400 text-sm mt-2">مثال: 1.0 = خصم يوم كامل، 1.5 = خصم يوم ونصف، 2.0 = خصم يومين</p>
            </div>

            {{-- Pro-rata Settings --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">الاحتساب التناسبي (Pro-rata)</h2>
                
                <label class="flex items-center gap-3 mb-4">
                    <input type="checkbox" wire:model="enableProrata" class="w-5 h-5 rounded text-indigo-600">
                    <span class="text-gray-700 dark:text-gray-300">تفعيل الاحتساب التناسبي للموظفين الجدد أو المستقيلين</span>
                </label>

                @if($enableProrata)
                    <div class="flex items-center gap-4">
                        <label class="text-gray-600 dark:text-gray-300">طريقة الاحتساب:</label>
                        <select wire:model="prorataMethod" class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="calendar">أيام تقويمية</option>
                            <option value="working">أيام عمل فعلية</option>
                        </select>
                    </div>
                @endif
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                    حفظ الإعدادات
                </button>
            </div>
        </form>
    </div>
</div>
