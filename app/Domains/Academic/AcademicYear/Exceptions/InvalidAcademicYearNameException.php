<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use Exception;

class InvalidAcademicYearNameException extends Exception
{
    public function __construct(?string $reason = null)
    {
        $message = 'صيغة اسم السنة غير صحيحة.';
        if ($reason) {
            $message = $reason;
        }

        parent::__construct($message);
    }
}
