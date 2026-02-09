<?php

namespace App\Domains\HR\Teacher\Data;

use App\Infrastructure\Data\BaseData;

class TeacherData extends BaseData
{
    public function __construct(
        public int $staff_id,
        public ?string $specialization = null,
        public ?string $bio = null,
    ) {
    }

    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return self::fromArray($request->validated());
    }
}
