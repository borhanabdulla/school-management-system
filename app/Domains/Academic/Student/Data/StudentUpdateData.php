<?php

namespace App\Domains\Academic\Student\Data;

use App\Infrastructure\Data\BaseData;

class StudentUpdateData extends BaseData
{
    public function __construct(
        public readonly string $first_name_ar,
        public readonly string $family_name_ar,
        public readonly string $date_of_birth,
        public readonly string $gender,
        public readonly ?int $nationality_id,
        public readonly ?string $blood_type,
        public readonly ?string $national_id,
    ) {
    }

    public static function fromForm($form): self
    {
        return new self(
            first_name_ar: $form->first_name_ar,
            family_name_ar: $form->family_name_ar,
            date_of_birth: $form->date_of_birth,
            gender: $form->gender,
            nationality_id: $form->nationality_id ?: null,
            blood_type: $form->blood_type ?: null,
            national_id: $form->national_id ?: null,
        );
    }
}
