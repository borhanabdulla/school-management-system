<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">الفواتير</h2>
            <p class="text-muted-foreground">إدارة فواتير الطلاب والرسوم الدراسية</p>
        </div>
        <div class="flex gap-2">
            <!-- Action Buttons (Future) -->
        </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-4 p-4 bg-surface rounded-xl border border-border">
        <div class="flex-1">
            <input wire:model.live="search" type="search" placeholder="بحث باسم الطالب أو ولي الأمر..." 
                   class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
        </div>
        <div class="w-48">
            <select wire:model.live="statusFilter" class="w-full px-4 py-2 bg-background border border-border rounded-lg">
                <option value="">جميع الحالات</option>
                <option value="unpaid">غير مدفوع</option>
                <option value="partially_paid">مدفوع جزئياً</option>
                <option value="paid">مدفوع بالكامل</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-surface rounded-xl border border-border overflow-hidden">
        <table class="w-full text-sm text-right">
            <thead class="bg-background/50 text-muted-foreground font-medium border-b border-border">
                <tr>
                    <th class="px-4 py-3">رقم الفاتورة</th>
                    <th class="px-4 py-3">الطالب</th>
                    <th class="px-4 py-3">السنة الدراسية</th>
                    <th class="px-4 py-3">الإجمالي</th>
                    <th class="px-4 py-3">المدفوع</th>
                    <th class="px-4 py-3">المتبقي</th>
                    <th class="px-4 py-3">الحالة</th>
                    <th class="px-4 py-3">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-primary/5 transition-colors">
                    <td class="px-4 py-3 font-mono text-primary">#{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3 font-medium">{{ $invoice->student->full_name_ar }}</td>
                    <td class="px-4 py-3">{{ $invoice->academicYear->name }}</td>
                    <td class="px-4 py-3 font-bold">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="px-4 py-3 text-emerald-600">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="px-4 py-3 text-red-600">{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusClass = match($invoice->status) {
                                \App\Domains\Finance\Enums\InvoiceStatus::Paid => 'bg-emerald-100 text-emerald-700',
                                \App\Domains\Finance\Enums\InvoiceStatus::PartiallyPaid => 'bg-amber-100 text-amber-700',
                                \App\Domains\Finance\Enums\InvoiceStatus::Unpaid => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                            $statusLabel = match($invoice->status) {
                                \App\Domains\Finance\Enums\InvoiceStatus::Paid => 'مسددة',
                                \App\Domains\Finance\Enums\InvoiceStatus::PartiallyPaid => 'جزئي',
                                \App\Domains\Finance\Enums\InvoiceStatus::Unpaid => 'غير مسددة',
                                default => $invoice->status->value ?? $invoice->status
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('finance.invoices.show', $invoice) }}" wire:navigate 
                           class="text-primary hover:text-primary-dark font-medium text-xs border border-primary/20 px-3 py-1 rounded-lg hover:bg-primary/10">
                            عرض
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-muted-foreground">
                        لا توجد فواتير مطابقة للبحث
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-border">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
