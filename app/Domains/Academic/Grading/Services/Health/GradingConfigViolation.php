<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Health;

final class GradingConfigViolation
{
    public const SEVERITY_INVALID = 'invalid';
    public const SEVERITY_WARNING = 'warning';

    public function __construct(
        private readonly string $type,
        private readonly string $message,
        private readonly array $meta = [],
        private readonly string $severity = self::SEVERITY_INVALID
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function meta(): array
    {
        return $this->meta;
    }

    public function severity(): string
    {
        return $this->severity;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'meta' => $this->meta,
            'severity' => $this->severity,
        ];
    }
}
