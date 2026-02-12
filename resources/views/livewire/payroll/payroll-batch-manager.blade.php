<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">دفعات الرواتب</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">إدارة دفعات الرواتب الشهرية وسير العمل</p>
            </div>
            <button wire:click="openGenerateModal" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                توليد دفعة رواتب
            </button>
        </div>

        {{-- Alerts --}}
        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <select wire:model.live="yearFilter" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
                <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">جميع الحالات</option>
                    <option value="draft">مسودة</option>
                    <option value="frozen">مجمد</option>
                    <option value="approved">معتمد</option>
                    <option value="paid">مصروف</option>
                </select>
            </div>
        </div>

        {{-- Batches Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($batches as $batch)
                @php
                    $statusConfig = [
                        'draft' => ['color' => 'yellow', 'icon' => '📝', 'label' => 'مسودة'],
                        'frozen' => ['color' => 'blue', 'icon' => '🔒', 'label' => 'مقفل'],
                        'approved' => ['color' => 'green', 'icon' => '✅', 'label' => 'معتمد'],
                        'paid' => ['color' => 'emerald', 'icon' => '💰', 'label' => 'تم الدفع'],
                    ];
                    $config = $statusConfig[$batch->status->value] ?? $statusConfig['draft'];
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all">
                    {{-- Card Header --}}
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">{{ $config['icon'] }}</span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 dark:bg-{{ $config['color'] }}-900/30 dark:text-{{ $config['color'] }}-400">
                                {{ $config['label'] }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $batch->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $batch->period_label }}</p>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">الموظفون</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $batch->employees_count }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">إجمالي الصافي</span>
                            <span class="font-bold text-green-600 dark:text-green-400">{{ number_format($batch->total_net, 2) }} ر.س</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">الاستقطاعات</span>
                            <span class="font-medium text-red-600 dark:text-red-400">{{ number_format($batch->total_deductions, 2) }} ر.س</span>
                        </div>
                    </div>

                    {{-- Card Actions --}}
                    <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <button wire:click="view({{ $batch->id }})" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm font-medium">
                                عرض التفاصيل
                            </button>
                            
                            <div class="flex items-center gap-2">
                                @if($batch->canFreeze())
                                    <button wire:click="freeze({{ $batch->id }})" wire:confirm="هل تريد قفل الدفعة للمراجعة؟ لن يمكن التعديل بعد ذلك." class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg transition-colors">
                                        قفل الدفعة
                                    </button>
                                @endif
                                
                                @if($batch->status === 'approved' || $batch->status === 'paid')
                                    <button wire:click="openExportModal({{ $batch->id }})" class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-sm rounded-lg transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        تصدير
                                    </button>
                                    <a href="{{ route('payroll.batches.receipt', $batch->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        طباعة
                                    </a>
                                @endif

                                @if($batch->canApprove())
                                    <button wire:click="approve({{ $batch->id }})" wire:confirm="هل تريد اعتماد الدفعة النهائية؟" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm rounded-lg transition-colors">
                                        اعتماد الدفعة
                                    </button>
                                @endif
                                @if($batch->canPay())
                                    <button wire:click="openPayModal({{ $batch->id }})" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm rounded-lg transition-colors">
                                        تسجيل الدفع
                                    </button>
                                @endif
                                @if($batch->status === 'draft')
                                    <button wire:click="delete({{ $batch->id }})" wire:confirm="هل تريد حذف الدفعة؟" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">لا توجد دفعات لهذا العام</p>
                    <button wire:click="openGenerateModal" class="mt-4 text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                        توليد دفعة رواتب
                    </button>
                </div>
            @endforelse
        </div>

        @if($batches->hasPages())
            <div class="mt-6">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

    {{-- Generate Modal --}}
    <x-ui.modal wire:model="showGenerateModal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">توليد دفعة رواتب جديدة</h2>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">السنة</label>
                        <select wire:model="generateYear" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الشهر</label>
                        <select wire:model="generateMonth" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @foreach(['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'] as $index => $month)
                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <p class="text-amber-700 dark:text-amber-300 text-sm">
                        <strong>ملاحظة:</strong> سيتم احتساب الرواتب بناءً على العقود النشطة وسجلات الحضور للفترة المحددة.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="$set('showGenerateModal', false)" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    إلغاء
                </button>
                <button wire:click="generate" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                    توليد الدفعة
                </button>
            </div>
        </div>
    </x-ui.modal>

    {{-- View Modal --}}
    @if($viewingBatch)
        <x-ui.modal wire:model="showViewModal" maxWidth="xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $viewingBatch->name }}</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $viewingBatch->status_color }}">
                        {{ $viewingBatch->status_label }}
                    </span>
                </div>

                {{-- Summary --}}
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $viewingBatch->employees_count }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">موظف</p>
                    </div>
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($viewingBatch->total_net, 2) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">إجمالي الصافي</p>
                    </div>
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg text-center">
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($viewingBatch->total_deductions, 2) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">الاستقطاعات</p>
                    </div>
                </div>

                {{-- Records Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الموظف</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الأساسي</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الاستقطاعات</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الصافي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($viewingBatch->records as $record)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $record->staff?->full_name ?? 'غير محدد' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ number_format($record->basic_salary, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-red-600 dark:text-red-400">{{ number_format($record->total_deductions, 2) }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($record->net_payable, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeViewModal" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        إغلاق
                    </button>
                </div>
            </div>
        </x-ui.modal>
    @endif

    {{-- Pay Modal (PR4.1) --}}
    <x-ui.modal wire:model="showPayModal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">تسجيل دفع الرواتب</h2>
            
            <div class="space-y-4">
                {{-- Payout Method --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">طريقة الصرف</label>
                    <select wire:model="payoutMethod" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="Cash">نقدي (Cash)</option>
                        <option value="Cheque">شيك (Cheque)</option>
                        <option value="BankTransfer">تحويل بنكي (Bank Transfer)</option>
                    </select>
                </div>

                {{-- Payout Reference --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المرجع / رقم العملية (اختياري)</label>
                    <input type="text" wire:model="payoutReference" 
                        placeholder="مثل: رقم الشيك أو رقم التحويل"
                        class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <p class="text-amber-700 dark:text-amber-300 text-sm">
                        <strong>تنبيه:</strong> بعد تسجيل الدفع، لن يمكن التراجع. تأكد من إتمام العملية فعلياً.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="closePayModal" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    إلغاء
                </button>
                <button wire:click="confirmPay" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
                    تأكيد الدفع
                </button>
            </div>
        </div>
    </x-ui.modal>
</div>
