<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Enums\InvoiceStatus;

/**
 * InvoiceTotalsService - خدمة موحدة لحساب حالة الفاتورة
 * 
 * مصدر واحد للحقيقة لحساب المبلغ المدفوع وحالة الفاتورة
 * 
 * @example
 * $service = app(InvoiceTotalsService::class);
 * $invoice = $service->recalculate($invoice);
 */
class InvoiceTotalsService
{
    /**
     * إعادة حساب المبلغ المدفوع وحالة الفاتورة
     * 
     * @param Invoice $invoice
     * @return Invoice الفاتورة بعد التحديث
     */
    public function recalculate(Invoice $invoice): Invoice
    {
        // 1. حساب المجموع الإجمالي (Gross)
        $grossAmount = $invoice->items()->sum('amount');

        // 2. حساب مجموع الخصومات المطبقة (Audit-based)
        $totalDiscounts = $invoice->items()
            ->join('discount_applications', 'invoice_items.id', '=', 'discount_applications.invoice_item_id')
            ->sum('discount_applications.applied_amount');

        // 3. حساب الصافي (Net)
        // في PR1 كان (total = gross). الآن (total = net).
        $netAmount = round(max(0, $grossAmount - $totalDiscounts), 2);

        // 4. المبلغ المدفوع (فقط الدفعات المؤكدة)
        $paidAmount = round($invoice->payments()
            ->where('status', \App\Domains\Finance\Enums\PaymentStatus::Posted)
            ->sum('amount'), 2);

        // 5. تحديد الحالة
        $status = match (true) {
            $paidAmount >= $netAmount => InvoiceStatus::Paid,
            $paidAmount > 0 => InvoiceStatus::PartiallyPaid,
            default => InvoiceStatus::Unpaid,
        };

        $invoice->update([
            'total_amount' => $netAmount,
            'paid_amount' => $paidAmount,
            'status' => $status,
        ]);

        return $invoice->fresh();
    }

    /**
     * التحقق مما إذا كانت الفاتورة مدفوعة بالكامل
     */
    public function isFullyPaid(Invoice $invoice): bool
    {
        return $invoice->paid_amount >= $invoice->total_amount;
    }

    /**
     * حساب المبلغ المتبقي للدفع
     */
    public function getRemainingAmount(Invoice $invoice): float
    {
        return max(0, $invoice->total_amount - $invoice->paid_amount);
    }
}
