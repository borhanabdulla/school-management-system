<?php

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Models\Invoice;

/**
 * InvoiceService - خدمات حسابية للفواتير
 * 
 * ملاحظة: عمليات الإنشاء موجودة في CreateInvoiceAction
 * 
 * @see \App\Domains\Finance\Actions\CreateInvoiceAction
 * @see \App\Domains\Finance\Actions\RecordPaymentAction
 */
class InvoiceService
{
    /**
     * حساب المبلغ المتبقي
     */
    public function calculateRemainingAmount(Invoice $invoice): float
    {
        return $invoice->total_amount - $invoice->paid_amount;
    }

    /**
     * التحقق من حالة التأخير
     */
    public function checkOverdueStatus(Invoice $invoice): bool
    {
        return $invoice->status !== \App\Domains\Finance\Enums\InvoiceStatus::Paid &&
            $invoice->due_date < now();
    }

    /**
     * حساب نسبة السداد
     */
    public function calculatePaymentPercentage(Invoice $invoice): float
    {
        if ($invoice->total_amount == 0) {
            return 100.0;
        }

        return round(($invoice->paid_amount / $invoice->total_amount) * 100, 2);
    }

    /**
     * تحديث حالات الفواتير المتأخرة
     * 
     * يُستدعى بواسطة Scheduled Command
     */
    public function markOverdueInvoices(): int
    {
        // استخدام Scope للمختصر + Enum
        return Invoice::outstanding()
            ->where('status', '!=', \App\Domains\Finance\Enums\InvoiceStatus::Overdue)
            ->where('due_date', '<', now())
            ->update(['status' => \App\Domains\Finance\Enums\InvoiceStatus::Overdue]);
    }
}
