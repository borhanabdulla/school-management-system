<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * InvoicePayerMismatchException
 * 
 * يُرمى عند محاولة الدفع من ولي أمر غير الدافع المثبت على الفاتورة
 */
class InvoicePayerMismatchException extends BusinessRuleException
{
    public function __construct(int $expectedPayerId, int $actualPayerId)
    {
        parent::__construct(
            "لا يمكن الدفع: الولي الحالي غير مطابق للدافع المثبت على الفاتورة",
            'INVOICE_PAYER_MISMATCH',
            [
                'expected_payer_id' => $expectedPayerId,
                'actual_payer_id' => $actualPayerId
            ]
        );
    }
}
