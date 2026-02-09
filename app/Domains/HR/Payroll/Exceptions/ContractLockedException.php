<?php

namespace App\Domains\HR\Payroll\Exceptions;

use Exception;

/**
 * ContractLockedException - استثناء العقد المقفل
 */
class ContractLockedException extends Exception
{
    protected $message = 'هذا العقد مقفل ولا يمكن تعديله. يرجى إنشاء ملحق عقد أو عقد جديد.';
    protected $code = 403;
}
