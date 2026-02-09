<?php

namespace Database\Factories;

use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Leave\Enums\LeaveRequestStatus;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Leave\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->addDay()->startOfDay();

        return [
            'staff_id' => Staff::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => 2,
            'reason' => $this->faker->sentence(6),
            'attachment' => null,
            'status' => LeaveRequestStatus::Pending,
            'approved_by' => null,
            'rejection_reason' => null,
        ];
    }
}
