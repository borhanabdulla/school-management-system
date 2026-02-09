<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use Exception;

class InsufficientTermsException extends Exception
{
    public function __construct(int $minimumRequired = 2)
    {
        parent::__construct("يجب إضافة {$minimumRequired} فصول دراسية على الأقل لتفعيل السنة");
    }
}
