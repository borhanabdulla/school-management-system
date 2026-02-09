<?php

namespace App\Domains\Academic\Grade\Data;

class GradeData
{
    public function __construct(
        public string $name,
        public int $level_order,
        public int $educational_stage_id,
        public ?int $next_grade_id,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            level_order: (int) $data['level_order'],
            educational_stage_id: (int) $data['educational_stage_id'],
            next_grade_id: !empty($data['next_grade_id']) ? (int) $data['next_grade_id'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'level_order' => $this->level_order,
            'educational_stage_id' => $this->educational_stage_id,
            'next_grade_id' => $this->next_grade_id,
        ];
    }
}
