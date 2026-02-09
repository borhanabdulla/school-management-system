<?php

declare(strict_types=1);

namespace App\Domains\Finance\Events;

use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * DiscountApplied - حدث تطبيق خصم على فاتورة
 * 
 * يُطلق عند تطبيق خصم لإبطال الكاش المالي
 * 
 * PR-A1: afterCommit لضمان عدم الإطلاق على transaction فاشلة
 */
class DiscountApplied implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Discount $discount,
        public readonly Invoice $invoice
    ) {
    }
}
