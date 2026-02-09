<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use Exception;

class YearNotDeletableException extends Exception
{
    public function __construct(?string $reason = null)
    {
        $message = 'لا يمكن حذف هذه السنة الدراسية';

        if ($reason) {
            $message = "لا يمكن حذف السنة الدراسية: {$reason}";
        }

        parent::__construct($message);
    }
}
