<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use Exception;

class InvalidDateRangeException extends Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? 'نطاق التواريخ غير صحيح: تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
    }
}
