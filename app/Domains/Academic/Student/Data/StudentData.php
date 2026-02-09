<?php

namespace App\Domains\Academic\Student\Data;

use App\Infrastructure\Data\BaseData;
use App\Domains\Academic\Student\Enums\StudentStatus;
use App\Domains\Shared\Enums\Gender;
use Carbon\Carbon;

class StudentData extends BaseData
{
    public function __construct(
        public readonly string $first_name_ar,
        public readonly string $family_name_ar,
        public readonly ?string $first_name_en = null,
        public readonly ?string $family_name_en = null,
        public readonly ?Carbon $date_of_birth = null,
        public readonly ?Gender $gender = null,
        public readonly ?int $nationality_id = null,
        public readonly ?string $national_id = null,
        public readonly ?string $passport_number = null,
        public readonly ?string $blood_type = null,
        public readonly ?int $current_grade_id = null,
        public readonly ?int $current_class_section_id = null,
        public readonly ?StudentStatus $status = null,
        public readonly ?string $admission_number = null,
        public readonly ?int $admission_application_id = null,
        public readonly ?int $user_id = null,
    ) {
    }

    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return self::fromArray($request->validated());
    }
}
