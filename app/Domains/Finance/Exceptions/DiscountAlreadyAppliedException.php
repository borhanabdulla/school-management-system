<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

class DiscountAlreadyAppliedException extends BusinessRuleException
{
    public function __construct(int $invoiceItemId)
    {
        parent::__construct(
            "تم تطبيق خصم مسبقاً على هذا البند (لا يسمح بتعدد الخصومات حالياً)",
            'DISCOUNT_ALREADY_APPLIED',
            ['invoice_item_id' => $invoiceItemId]
        );
    }
}
