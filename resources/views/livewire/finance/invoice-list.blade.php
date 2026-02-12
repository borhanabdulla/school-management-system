<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">الفواتير</h2>
            <p class="text-muted-foreground">إدارة فواتير الطلاب والرسوم الدراسية</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Amount --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي الفواتير</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->summary['total'], 2) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $this->summary['count'] }} فاتورة</p>
        </div>

        {{-- Paid Amount --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">المحصّل</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($this->summary['paid'], 2) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $this->summary['paid_count'] }} فاتورة مسددة</p>
        </div>

        {{-- Remaining Amount --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">المتبقي</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($this->summary['remaining'], 2) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $this->summary['outstanding_count'] }} فاتورة معلقة</p>
        </div>

        {{-- Collection Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">نسبة التحصيل</span>
                <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            @php
                $rate = $this->summary['total'] > 0 ? round(($this->summary['paid'] / $this->summary['total']) * 100, 1) : 0;
            @endphp
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $rate }}%</p>
            <div class="mt-2 h-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full transition-all duration-500" style="width: {{ min($rate, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="flex-1 min-w-[200px]">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="بحث باسم الطالب..."
                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="min-w-[160px]">
            <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">جميع الحالات</option>
                @foreach(\App\Domains\Finance\Enums\InvoiceStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[160px]">
            <select wire:model.live="yearFilter"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">جميع السنوات</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="text-right px-4 py-3 font-bold text-gray-600 dark:text-gray-400">رقم الفاتورة</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600 dark:text-gray-400">الطالب</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600 dark:text-gray-400">السنة الدراسية</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600 dark:text-gray-400">المبلغ</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600 dark:text-gray-400">المدفوع</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600 dark:text-gray-400">الحالة</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600 dark:text-gray-400">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs font-bold text-gray-700 dark:text-gray-300">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                                {{ $invoice->student?->first_name_ar }} {{ $invoice->student?->family_name_ar }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $invoice->academicYear?->name }}
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                {{ number_format($invoice->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($invoice->paid_amount, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'unpaid' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        'partially_paid' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'overdue' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                    ];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$invoice->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $invoice->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('finance.invoices.show', $invoice) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs font-bold transition-colors">
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">لا توجد فواتير</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">جرّب تغيير الفلاتر أو البحث</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
