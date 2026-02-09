<?php

namespace App\Domains\Academic\Student\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا يوجد ولي أمر على الأقل عند تسجيل طالب
 */
class MissingGuardianException extends InvalidOperationException
{
    public function __construct()
    {
        parent::__construct(
            'يجب توفير ولي أمر واحد على الأقل لتسجيل الطالب.',
            'register_student',
            'missing_guardian'
        );
    }
}
