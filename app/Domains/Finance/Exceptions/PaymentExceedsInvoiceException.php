<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * PaymentExceedsInvoiceException - مبلغ الدفعة يتجاوز المتبقي
 */
class PaymentExceedsInvoiceException extends BusinessRuleException
{
    public function __construct(
        public readonly float $paymentAmount,
        public readonly float $remainingAmount
    ) {
        parent::__construct(
            "مبلغ الدفعة ({$paymentAmount}) يتجاوز المتبقي من الفاتورة ({$remainingAmount})",
            'PAYMENT_EXCEEDS_INVOICE'
        );
    }
}
