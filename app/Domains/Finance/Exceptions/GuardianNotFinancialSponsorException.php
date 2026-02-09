<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * GuardianNotFinancialSponsorException
 * 
 * يُرمى عند محاولة الدفع من ولي غير مسؤول مالياً عن الطالب
 */
class GuardianNotFinancialSponsorException extends BusinessRuleException
{
    public function __construct(?int $guardianId = null)
    {
        parent::__construct(
            'الولي المحدد ليس المسؤول المالي لهذا الطالب',
            'GUARDIAN_NOT_FINANCIAL_SPONSOR',
            $guardianId ? ['guardian_id' => $guardianId] : []
        );
    }
}
