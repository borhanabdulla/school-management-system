<?php

namespace App\Domains\HR\Teacher\Data;

use App\Infrastructure\Data\BaseData;

class TeacherOnboardingData extends BaseData
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $specialization,
        public readonly int $max_weekly_classes,
        public readonly string $hire_date,
        public readonly ?string $phone = null,
        public readonly mixed $photo = null,
    ) {
    }

    public static function fromForm($form): self
    {
        return new self(
            first_name: $form->first_name,
            last_name: $form->last_name,
            email: $form->email,
            password: $form->password,
            specialization: $form->specialization,
            max_weekly_classes: $form->max_weekly_classes,
            hire_date: $form->hire_date,
            phone: $form->phone,
            photo: $form->photo,
        );
    }
}
