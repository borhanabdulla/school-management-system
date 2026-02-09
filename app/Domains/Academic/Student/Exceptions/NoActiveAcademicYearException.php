<?php

namespace App\Domains\Academic\Student\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا توجد سنة أكاديمية نشطة
 */
class NoActiveAcademicYearException extends InvalidOperationException
{
    public function __construct()
    {
        parent::__construct(
            'لا توجد سنة أكاديمية نشطة.',
            'create_enrollment',
            'no_active_year'
        );
    }
}
