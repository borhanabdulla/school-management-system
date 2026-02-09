<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

class DiscountExceedsItemAmountException extends BusinessRuleException
{
    public function __construct(float $discountAmount, float $itemAmount)
    {
        parent::__construct(
            "قيمة الخصم ({$discountAmount}) تتجاوز قيمة البند ({$itemAmount})",
            'DISCOUNT_EXCEEDS_ITEM_AMOUNT',
            ['discount_amount' => $discountAmount, 'item_amount' => $itemAmount]
        );
    }
}
