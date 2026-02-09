<?php

namespace App\Domains\Academic\Student\Data;

use App\Infrastructure\Data\BaseData;

class StudentRegistrationData extends BaseData
{
    public function __construct(
        public readonly array $student,
        public readonly array $guardians,
        public readonly int $grade_id,
        public readonly ?int $class_section_id,
        public readonly array $health_data,
        public readonly array $address,
        public readonly array $documents,
        public readonly bool $is_transfer,
        public readonly ?array $previous_history,
        public readonly bool $create_invoice = true
    ) {
    }

    public static function fromLivewire($form, $guardians, $healthConditions, $address, $documents, $isTransfer, $previousHistory): self
    {
        return new self(
            student: [
                'first_name_ar' => $form->first_name_ar,
                'family_name_ar' => $form->family_name_ar,
                'date_of_birth' => $form->date_of_birth,
                'gender' => $form->gender,
                'nationality_id' => $form->nationality_id ?: null,
                'blood_type' => $form->blood_type,
                'national_id' => $form->national_id ?: null,
            ],
            guardians: $guardians,
            grade_id: $form->grade_id,
            class_section_id: $form->class_section_id ?: null,
            health_data: $healthConditions,
            address: $address,
            documents: $documents,
            is_transfer: $isTransfer,
            previous_history: $isTransfer ? $previousHistory : null,
            create_invoice: true
        );
    }
}
