<?php

namespace App\Domains\Academic\ClassSection\Data;

use App\Domains\Academic\ClassSection\Enums\SectionGenderType;

class ClassSectionData
{
    public function __construct(
        public string $name,
        public int $grade_id,
        public int $academic_year_id,
        public int $max_capacity,
        public SectionGenderType $gender_type,
        public bool $is_active,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            grade_id: (int) $data['grade_id'],
            academic_year_id: (int) $data['academic_year_id'],
            max_capacity: (int) $data['max_capacity'],
            gender_type: $data['gender_type'] instanceof SectionGenderType
            ? $data['gender_type']
            : SectionGenderType::from($data['gender_type']),
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'grade_id' => $this->grade_id,
            'academic_year_id' => $this->academic_year_id,
            'max_capacity' => $this->max_capacity,
            'gender_type' => $this->gender_type,
            'is_active' => $this->is_active,
        ];
    }
}
