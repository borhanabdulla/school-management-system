<?php

namespace App\Domains\HR\Payroll\Exceptions;

use Exception;

/**
 * InvalidWorkflowStateException - استثناء حالة سير العمل غير صالحة
 */
class InvalidWorkflowStateException extends Exception
{
    protected $code = 422;

    public function __construct(string $message = 'الحالة الحالية لا تسمح بهذا الإجراء.')
    {
        parent::__construct($message, $this->code);
    }
}
