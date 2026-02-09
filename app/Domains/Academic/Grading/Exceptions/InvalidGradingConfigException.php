<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Exceptions;

use RuntimeException;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;

class InvalidGradingConfigException extends RuntimeException
{
    /**
     * @param array<int, GradingConfigViolation> $violations
     */
    public function __construct(
        private readonly array $violations
    ) {
        parent::__construct('Invalid grading configuration detected.');
    }

    /**
     * @return array<int, GradingConfigViolation>
     */
    public function violations(): array
    {
        return $this->violations;
    }
}
