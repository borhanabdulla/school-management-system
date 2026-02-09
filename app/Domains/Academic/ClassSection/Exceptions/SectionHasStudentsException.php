<?php

namespace App\Domains\Academic\ClassSection\Exceptions;

use Exception;

class SectionHasStudentsException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('لا يمكن حذف الشعبة لأنها تحتوي على طلاب مسجلين.'));
    }
}
