<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما تكون الدورة الامتحانية غير نشطة
 */
class ExamSessionNotActiveException extends InvalidOperationException
{
    public function __construct()
    {
        parent::__construct(
            'الدورة الامتحانية غير نشطة. لا يمكن الرصد الآن.',
            'submit_grade',
            'session_not_active'
        );
    }
}
