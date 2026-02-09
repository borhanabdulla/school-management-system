<?php

namespace App\Domains\Academic\Stage\Data;

class StageData
{
    public function __construct(
        public string $name,
        public int $rank,
        public float $min_passing_percentage,
        public string $grading_system,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            rank: (int) $data['rank'],
            min_passing_percentage: (float) $data['min_passing_percentage'],
            grading_system: $data['grading_system'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'rank' => $this->rank,
            'min_passing_percentage' => $this->min_passing_percentage,
            'grading_system' => $this->grading_system,
        ];
    }
}
