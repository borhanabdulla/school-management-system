<div class="space-y-6 animate-fade-in-up">
    <div class="text-center mb-8">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">الرسوم والفواتير</h3>
        <p class="text-gray-500 dark:text-white/60">مراجعة الرسوم الدراسية وإصدار الفاتورة المبدئية</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- قائمة الرسوم -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-white/5 backdrop-blur-xl border border-gray-200 dark:border-white/10 rounded-2xl p-6 shadow-sm dark:shadow-none">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-500/20 text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </span>
                    الرسوم المستحقة
                </h4>

                @if (count($applicable_fees) > 0)
                    <div class="space-y-3">
                        @foreach ($applicable_fees as $fee)
                            <label
                                class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors cursor-pointer group">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" wire:model.live="selected_fee_types"
                                            value="{{ $fee->id }}"
                                            class="w-5 h-5 rounded-lg border-gray-300 dark:border-white/20 bg-white dark:bg-white/10 text-blue-500 focus:ring-blue-500/50 focus:ring-offset-0 transition-all">
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {{ $fee->feeType->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-white/50">استحقاق:
                                            {{ $fee->due_date ? \Carbon\Carbon::parse($fee->due_date)->format('Y-m-d') : 'عند التسجيل' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="font-bold text-emerald-600 dark:text-emerald-400 text-lg">
                                    {{ number_format($fee->amount, 2) }} <span
                                        class="text-xs text-emerald-600/60 dark:text-emerald-400/60">ر.س</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div
                        class="text-center py-8 text-gray-500 dark:text-white/40 bg-gray-50 dark:bg-white/5 rounded-xl border border-dashed border-gray-200 dark:border-white/10">
                        لا توجد رسوم محددة لهذا الصف الدراسي حالياً.
                    </div>
                @endif
            </div>

            <!-- الخصومات -->
            <div class="bg-white dark:bg-white/5 backdrop-blur-xl border border-gray-200 dark:border-white/10 rounded-2xl p-6 shadow-sm dark:shadow-none">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-purple-500/20 text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                    </span>
                    الخصومات
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">قيمة الخصم المباشر</label>
                        <div class="relative">
                            <input type="number" wire:model.live.debounce.500ms="discount_amount" min="0"
                                step="0.01"
                                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:border-purple-500/50 focus:ring-2 focus:ring-purple-500/20 transition-all">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-white/40 text-sm">ر.س</span>
                            </div>
                        </div>
                    </div>
                    <!-- يمكن إضافة حقل لسبب الخصم هنا مستقبلاً -->
                </div>
            </div>
        </div>

        <!-- ملخص الفاتورة -->
        <div class="space-y-4">
            <div
                class="bg-white dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 border border-gray-200 dark:border-white/10 rounded-2xl p-6 shadow-xl sticky top-6">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-6 text-center">ملخص الفاتورة</h4>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600 dark:text-white/70">
                        <span>المجموع الفرعي</span>
                        <span>{{ number_format(collect($applicable_fees)->whereIn('id', $selected_fee_types)->sum('amount'), 2) }}
                            ر.س</span>
                    </div>
                    <div class="flex justify-between text-purple-400">
                        <span>الخصم</span>
                        <span>- {{ number_format($discount_amount, 2) }} ر.س</span>
                    </div>
                    <div class="h-px bg-gray-200 dark:bg-white/10 my-2"></div>
                    <div class="flex justify-between text-xl font-bold text-gray-900 dark:text-white">
                        <span>الإجمالي النهائي</span>
                        <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($final_total, 2) }} ر.س</span>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative flex items-center">
                            <input type="checkbox" wire:model="create_invoice"
                                class="w-5 h-5 rounded-lg border-gray-300 dark:border-white/20 bg-white dark:bg-white/10 text-emerald-500 focus:ring-emerald-500/50 focus:ring-offset-0 transition-all">
                        </div>
                        <span class="text-sm text-gray-700 dark:text-white font-medium">إصدار فاتورة فورية</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-white/40 mt-2 mr-8">سيتم إنشاء فاتورة "غير مدفوعة" في النظام المالي للطالب.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
