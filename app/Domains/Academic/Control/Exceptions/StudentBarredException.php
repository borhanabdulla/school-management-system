<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما يكون الطالب محروماً من الامتحانات
 */
class StudentBarredException extends InvalidOperationException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            "هذا الطالب محروم من الامتحانات. السبب: {$reason}",
            'submit_grade',
            'student_barred'
        );
    }
}
