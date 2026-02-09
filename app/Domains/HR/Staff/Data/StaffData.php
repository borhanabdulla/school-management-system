<?php

namespace App\Domains\HR\Staff\Data;

use App\Infrastructure\Data\BaseData;
use Carbon\Carbon;

class StaffData extends BaseData
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $employee_number,
        public ?string $phone = null,
        public ?Carbon $joining_date = null,
        public ?int $work_shift_id = null,
        public ?string $employment_type = null,
        public ?string $job_title = null,
        public \App\Domains\HR\Staff\Enums\StaffStatus|string|null $status = \App\Domains\HR\Staff\Enums\StaffStatus::Active,
        public ?int $user_id = null,
    ) {
    }

    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return self::fromArray($request->validated());
    }
}
