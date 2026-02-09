<?php

namespace App\Domains\HR\Payroll\Exceptions;

use Exception;

/**
 * DuplicatePayrollException - استثناء تكرار مسير الرواتب
 */
class DuplicatePayrollException extends Exception
{
    protected $message = 'يوجد مسير رواتب لهذه الفترة بالفعل.';
    protected $code = 409;
}
