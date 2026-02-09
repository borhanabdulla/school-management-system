<?php

namespace App\Domains\Academic\Promotion\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا يمكن ترحيل طالب معين
 */
class StudentCannotBePromotedException extends InvalidOperationException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            $reason,
            'promotion',
            'student_cannot_be_promoted'
        );
    }
}
