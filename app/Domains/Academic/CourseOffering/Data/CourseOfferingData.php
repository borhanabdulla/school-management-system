<?php

namespace App\Domains\Academic\CourseOffering\Data;

class CourseOfferingData
{
    public function __construct(
        public int $academic_year_id,
        public int $term_id,
        public int $class_section_id,
        public int $subject_id,
        public ?int $teacher_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            academic_year_id: (int) $data['academic_year_id'],
            term_id: (int) $data['term_id'],
            class_section_id: (int) $data['class_section_id'],
            subject_id: (int) $data['subject_id'],
            teacher_id: isset($data['teacher_id']) ? (int) $data['teacher_id'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'academic_year_id' => $this->academic_year_id,
            'term_id' => $this->term_id,
            'class_section_id' => $this->class_section_id,
            'subject_id' => $this->subject_id,
            'teacher_id' => $this->teacher_id,
        ];
    }
}
