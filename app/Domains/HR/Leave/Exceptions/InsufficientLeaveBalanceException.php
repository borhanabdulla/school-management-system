<?php

namespace App\Domains\HR\Leave\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما يكون رصيد الإجازة غير كافٍ
 */
class InsufficientLeaveBalanceException extends InvalidOperationException
{
    public function __construct(int $remaining, int $requested)
    {
        parent::__construct(
            "رصيد الإجازة غير كافٍ. المتبقي: {$remaining} يوم، المطلوب: {$requested} يوم.",
            'approve_leave',
            'insufficient_balance'
        );
    }

    /**
     * عند الموافقة على الطلب
     */
    public static function onApproval(): self
    {
        return new self(0, 0);
    }
}
