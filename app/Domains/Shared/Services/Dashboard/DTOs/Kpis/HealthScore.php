<?php

namespace App\Domains\Shared\Services\Dashboard\DTOs\Kpis;

final readonly class HealthScore
{
    public function __construct(
        public int $score,
        public string $label,
        public string $color
    ) {
    }

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'label' => $this->label,
            'color' => $this->color,
        ];
    }
}
