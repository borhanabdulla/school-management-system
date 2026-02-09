<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Exceptions;

use Exception;

/**
 * GradingException - الاستثناء الأساسي لدومين الدرجات
 */
class GradingException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
