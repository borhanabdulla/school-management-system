<?php

namespace App\Domains\HR\Payroll\Exceptions;

use Exception;

class AcademicYearNotFoundException extends Exception
{
    protected $message = 'لا يمكن تحديد السنة الأكاديمية للفترة المحددة';
}
