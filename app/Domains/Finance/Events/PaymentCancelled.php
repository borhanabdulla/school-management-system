<?php

namespace App\Domains\Finance\Events;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/**
 * PR6: afterCommit لضمان عدم الإطلاق على transaction فاشلة
 */
class PaymentCancelled implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
        public readonly Invoice $invoice
    ) {
    }
}
