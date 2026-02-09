<?php

declare(strict_types=1);

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

/**
 * AcademicYearClosedException
 * 
 * يُرمى عند محاولة تنفيذ عملية مالية على سنة دراسية مغلقة
 */
class AcademicYearClosedException extends BusinessRuleException
{
    public function __construct()
    {
        parent::__construct(
            'لا يمكن تنفيذ العملية: السنة الدراسية مغلقة',
            'ACADEMIC_YEAR_CLOSED'
        );
    }
}
