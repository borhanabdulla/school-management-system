<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use Exception;

class DateOverlapException extends Exception
{
    public function __construct(?string $overlappingYearName = null)
    {
        $message = 'يوجد تداخل في التواريخ مع سنة دراسية أخرى مسجلة';

        if ($overlappingYearName) {
            $message = "يوجد تداخل في التواريخ مع السنة الدراسية: {$overlappingYearName}";
        }

        parent::__construct($message);
    }
}
