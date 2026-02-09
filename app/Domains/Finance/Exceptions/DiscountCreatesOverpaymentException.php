<?php

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

class DiscountCreatesOverpaymentException extends BusinessRuleException
{
    public function __construct(float $paidAmount, float $newNetAmount)
    {
        parent::__construct(
            "Cannot apply discount. Paid amount ({$paidAmount}) would exceed the new net amount ({$newNetAmount}). Strict no-overpayment policy.",
            'DISCOUNT_CREATES_OVERPAYMENT',
            ['paid_amount' => $paidAmount, 'new_net_amount' => $newNetAmount]
        );
    }
}
