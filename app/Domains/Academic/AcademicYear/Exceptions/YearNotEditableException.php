<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use Exception;

class YearNotEditableException extends Exception
{
    public function __construct(?string $reason = null)
    {
        $message = 'لا يمكن تعديل هذه السنة الدراسية';

        if ($reason) {
            $message = "لا يمكن تعديل السنة الدراسية: {$reason}";
        }

        parent::__construct($message);
    }
}
