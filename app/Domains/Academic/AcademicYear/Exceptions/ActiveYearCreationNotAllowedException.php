<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use Exception;

class ActiveYearCreationNotAllowedException extends Exception
{
    public function __construct()
    {
        parent::__construct('لا يمكن إنشاء سنة دراسية بهذه الحالة.');
    }
}
