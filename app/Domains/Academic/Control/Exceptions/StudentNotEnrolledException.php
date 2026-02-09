<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا يكون الطالب مسجلاً في الدورة الامتحانية
 */
class StudentNotEnrolledException extends InvalidOperationException
{
    public function __construct(int $studentId, int $sessionId)
    {
        parent::__construct(
            'الطالب غير مسجل في هذه الدورة الامتحانية.',
            'process_result',
            'student_not_enrolled'
        );
    }
}
