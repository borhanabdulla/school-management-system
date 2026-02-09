<?php

declare(strict_types=1);

namespace App\Domains\Finance\Events;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/**
 * PaymentReceived - حدث استلام دفعة مالية
 * 
 * يُطلق عند تسجيل دفعة جديدة على فاتورة
 * يمكن استخدامه لإرسال إيصال أو تحديث حالة الطالب
 * 
 * PR6: afterCommit لضمان عدم الإطلاق على transaction فاشلة
 */
class PaymentReceived implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
        public readonly Invoice $invoice
    ) {
    }
}
