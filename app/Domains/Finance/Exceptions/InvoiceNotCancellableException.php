<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * InvoiceNotCancellableException - لا يمكن إلغاء الفاتورة
 */
class InvoiceNotCancellableException extends BusinessRuleException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            "لا يمكن إلغاء الفاتورة: {$reason}",
            'INVOICE_NOT_CANCELLABLE'
        );
    }
}
