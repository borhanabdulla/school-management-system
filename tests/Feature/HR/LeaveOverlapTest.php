<?php

namespace Tests\Feature\HR;

use Tests\TestCase;
use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Services\LeaveService;
use App\Domains\HR\Leave\Enums\LeaveRequestStatus;
use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class LeaveOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_request_rejects_overlapping_pending_leave(): void
    {
        $staff = Staff::factory()->create();
        $leaveType = LeaveType::factory()->create(['days_per_year' => 10]);

        $start = Carbon::now()->addDays(5)->startOfDay();
        $end = $start->copy()->addDays(2);

        LeaveRequest::factory()->create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => 3,
            'status' => LeaveRequestStatus::Pending,
        ]);

        $service = app(LeaveService::class);

        $this->expectException(ValidationException::class);

        $service->submitRequest([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start->copy()->addDay()->toDateString(),
            'end_date' => $end->copy()->addDay()->toDateString(),
            'reason' => 'سبب الاختبار',
        ]);
    }
}
