<?php

namespace Database\Factories;

use App\Domains\HR\Staff\Models\StaffAttendance;
use App\Domains\HR\Staff\Enums\StaffAttendanceStatus;
use App\Domains\HR\Staff\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StaffAttendanceFactory extends Factory
{
    protected $model = StaffAttendance::class;

    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'date' => Carbon::now()->toDateString(),
            'check_in' => '08:00',
            'check_out' => '14:00',
            'status' => StaffAttendanceStatus::Present,
            'delay_minutes' => 0,
            'early_leave_minutes' => 0,
            'source' => 'manual',
            'recorded_by' => User::factory(),
            'remarks' => null,
        ];
    }
}
