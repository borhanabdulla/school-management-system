<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما يكون الرقم السري غير موجود
 */
class InvalidSecretNumberException extends InvalidOperationException
{
    public function __construct(string $secretNumber, int $sessionId)
    {
        parent::__construct(
            "الرقم السري [{$secretNumber}] غير موجود في هذه الدورة.",
            'submit_grade',
            'invalid_secret_number'
        );
    }
}
