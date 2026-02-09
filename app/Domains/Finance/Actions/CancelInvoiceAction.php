<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Exceptions\InvoiceNotCancellableException;
use Illuminate\Support\Facades\DB;

/**
 * CancelInvoiceAction - إلغاء فاتورة
 * 
 * تُلغي فاتورة مع التحقق من عدم وجود مدفوعات
 */
class CancelInvoiceAction
{
    public function execute(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            // 1. التحقق من عدم وجود مدفوعات
            if ($invoice->paid_amount > 0) {
                throw new InvoiceNotCancellableException('يوجد مدفوعات مسجلة على هذه الفاتورة');
            }

            // 2. التحقق من أنها ليست ملغاة بالفعل
            if ($invoice->status === \App\Domains\Finance\Enums\InvoiceStatus::Cancelled) {
                throw new InvoiceNotCancellableException('الفاتورة ملغاة بالفعل');
            }

            // 3. إلغاء الفاتورة
            $invoice->update(['status' => \App\Domains\Finance\Enums\InvoiceStatus::Cancelled]);

            return $invoice->fresh();
        });
    }
}
