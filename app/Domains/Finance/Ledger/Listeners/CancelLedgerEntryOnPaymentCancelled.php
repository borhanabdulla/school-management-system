<?php

namespace App\Domains\Finance\Ledger\Listeners;

use App\Domains\Finance\Events\PaymentCancelled;
use App\Domains\Finance\Ledger\Services\LedgerService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * عند إلغاء دفعة طالب، نلغي قيد الـ Ledger المقابل
 */
class CancelLedgerEntryOnPaymentCancelled implements ShouldQueue
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {
    }

    public function handle(PaymentCancelled $event): void
    {
        $payment = $event->payment;

        // إلغاء القيد باستخدام external_key
        $this->ledgerService->cancelByExternalKey(
            externalKey: "payment:{$payment->id}",
            cancelledBy: $payment->cancelled_by ?? auth()->id() ?? 1,
            reason: $payment->cancel_reason ?? 'إلغاء الدفعة',
        );
    }
}
