<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * DuplicatePaymentException
 * 
 * يُرمى عند محاولة تسجيل دفعة بمرجع معاملة مكرر
 */
class DuplicatePaymentException extends BusinessRuleException
{
    public function __construct(string $transactionReference)
    {
        parent::__construct(
            "معاملة بنفس المرجع موجودة مسبقاً: {$transactionReference}",
            'DUPLICATE_TRANSACTION_REFERENCE',
            ['transaction_reference' => $transactionReference]
        );
    }
}
