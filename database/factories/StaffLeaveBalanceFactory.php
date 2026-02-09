<?php

namespace Database\Factories;

use App\Domains\HR\Leave\Models\StaffLeaveBalance;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Leave\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StaffLeaveBalanceFactory extends Factory
{
    protected $model = StaffLeaveBalance::class;

    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'leave_type_id' => LeaveType::factory(),
            'year' => Carbon::now()->year,
            'remaining_days' => 10,
        ];
    }
}
