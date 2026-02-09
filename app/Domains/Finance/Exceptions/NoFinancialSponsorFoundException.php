<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * NoFinancialSponsorFoundException
 * 
 * يُرمى عند عدم وجود مسؤول مالي للطالب (مما يمنع إنشاء الفاتورة)
 */
class NoFinancialSponsorFoundException extends BusinessRuleException
{
    public function __construct(int $studentId)
    {
        parent::__construct(
            "لا يمكن إنشاء الفاتورة: الطالب ليس لديه مسؤول مالي محدد",
            'NO_FINANCIAL_SPONSOR_FOUND',
            ['student_id' => $studentId]
        );
    }
}
