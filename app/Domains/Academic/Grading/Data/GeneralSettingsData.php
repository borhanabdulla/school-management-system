<?php

namespace App\Domains\Academic\Grading\Data;

final class GeneralSettingsData
{
    public function __construct(
        public readonly float $defaultPassScore,
        public readonly int $graceMarksLimit,
        public readonly array $termWeights
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            defaultPassScore: (int) ($payload['defaultPassScore'] ?? 50),
            graceMarksLimit: (int) ($payload['graceMarksLimit'] ?? 2),
            termWeights: (array) ($payload['termWeights'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'defaultPassScore' => $this->defaultPassScore,
            'graceMarksLimit' => $this->graceMarksLimit,
            'termWeights' => $this->termWeights,
        ];
    }
}
