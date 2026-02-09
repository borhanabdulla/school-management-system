<?php

namespace App\Domains\HR\Payroll\Exceptions;

use Exception;

/**
 * PayrollFreezeException - استثناء تجميد الفترة المحاسبية
 */
class PayrollFreezeException extends Exception
{
    protected $message = 'تم تجميد هذه الفترة المحاسبية. لا يمكن إجراء أي تعديلات.';
    protected $code = 403;
}
