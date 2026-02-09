<?php

namespace App\Domains\Academic\Subject\Data;

class SubjectAssignmentData
{
    public function __construct(
        public int $subject_id,
        public int $credit_hours = 1,
        public string $term_type = 'full_year',
        public bool $is_active = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            subject_id: (int) $data['subject_id'],
            credit_hours: (int) ($data['credit_hours'] ?? 1),
            term_type: $data['term_type'] ?? 'full_year',
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'subject_id' => $this->subject_id,
            'credit_hours' => $this->credit_hours,
            'term_type' => $this->term_type,
            'is_active' => $this->is_active,
        ];
    }
}
