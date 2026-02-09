<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * MultipleFinancialSponsorsException
 * 
 * يُرمى عند وجود أكثر من مسؤول مالي للطالب (حالة بيانات غير صحيحة)
 */
class MultipleFinancialSponsorsException extends BusinessRuleException
{
    public function __construct(int $studentId, int $count)
    {
        parent::__construct(
            "بيانات غير صحيحة: يوجد أكثر من مسؤول مالي للطالب ({$count})",
            'MULTIPLE_FINANCIAL_SPONSORS',
            [
                'student_id' => $studentId,
                'sponsors_count' => $count
            ]
        );
    }
}
