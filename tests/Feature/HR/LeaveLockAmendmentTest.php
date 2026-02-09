<?php

namespace Tests\Feature\HR;

use Tests\TestCase;
use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Models\StaffLeaveBalance;
use App\Domains\HR\Leave\Services\LeaveService;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Exceptions\PeriodLockedException;
use App\Domains\HR\Shared\Models\HrAmendment;
use App\Domains\HR\Staff\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class LeaveLockAmendmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_leave_in_locked_period_creates_amendment(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create();
        $leaveType = LeaveType::factory()->create(['days_per_year' => 10]);

        $start = Carbon::now()->startOfMonth()->addDay();
        $end = $start->copy();

        PayrollBatch::factory()->create([
            'period_start' => $start->copy()->startOfMonth(),
            'period_end' => $start->copy()->endOfMonth(),
            'year' => (int) $start->year,
            'month' => (int) $start->month,
            'status' => PayrollBatchStatus::Approved,
        ]);

        StaffLeaveBalance::factory()->create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => (int) $start->year,
            'remaining_days' => 10,
        ]);

        $request = LeaveRequest::factory()->create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => 1,
        ]);

        $service = app(LeaveService::class);

        try {
            $service->approveRequest($request, $user->id);
            $this->fail('Expected PeriodLockedException was not thrown.');
        } catch (PeriodLockedException $e) {
            $this->assertStringContainsString($start->format('Y-m'), $e->getMessage());
        }

        $amendment = HrAmendment::where('amendable_type', LeaveRequest::class)
            ->where('amendable_id', $request->id)
            ->first();

        $this->assertNotNull($amendment);
        $this->assertEquals('leave', $amendment->kind);
        $this->assertEquals($staff->id, $amendment->staff_id);
        $this->assertEquals($user->id, $amendment->requested_by);
        $this->assertEquals('pending', $amendment->status);
    }
}
