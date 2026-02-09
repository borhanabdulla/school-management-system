<?php

namespace App\Domains\Academic\Promotion\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما تكون المدرسة غير جاهزة للترحيل
 */
class SchoolNotReadyException extends InvalidOperationException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            'المدرسة غير جاهزة للترحيل: ' . $reason,
            'promotion',
            'school_not_ready'
        );
    }
}
