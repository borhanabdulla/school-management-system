<?php

namespace App\Domains\Academic\Grading\Data;

final class GradeScaleData
{
    public function __construct(public readonly array $scale)
    {
    }

    public static function fromArray(array $payload): self
    {
        return new self(scale: (array) ($payload['scale'] ?? []));
    }

    public function toArray(): array
    {
        return $this->scale;
    }
}
