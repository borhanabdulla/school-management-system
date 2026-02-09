<?php

namespace Tests\Feature\HR;

use Tests\TestCase;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Models\StaffAttendance;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Exceptions\PeriodLockedException;
use App\Domains\HR\Attendance\Services\AttendanceService;
use App\Domains\HR\Shared\Models\HrAmendment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceLockAmendmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_attendance_in_locked_period_creates_amendment(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create();

        $date = Carbon::now()->startOfMonth()->addDay();

        PayrollBatch::factory()->create([
            'period_start' => $date->copy()->startOfMonth(),
            'period_end' => $date->copy()->endOfMonth(),
            'year' => (int) $date->year,
            'month' => (int) $date->month,
            'status' => PayrollBatchStatus::Approved,
        ]);

        $attendance = StaffAttendance::factory()->create([
            'staff_id' => $staff->id,
            'date' => $date->toDateString(),
        ]);

        $service = app(AttendanceService::class);

        try {
            $service->correctAttendance(
                $attendance->id,
                ['check_in' => '08:30'],
                $user->id,
                'تصحيح بعد اعتماد المسير'
            );
            $this->fail('Expected PeriodLockedException was not thrown.');
        } catch (PeriodLockedException $e) {
            $this->assertStringContainsString($date->format('Y-m'), $e->getMessage());
        }

        $amendment = HrAmendment::where('amendable_type', StaffAttendance::class)
            ->where('amendable_id', $attendance->id)
            ->first();

        $this->assertNotNull($amendment);
        $this->assertEquals('attendance', $amendment->kind);
        $this->assertEquals($staff->id, $amendment->staff_id);
        $this->assertEquals($user->id, $amendment->requested_by);
        $this->assertEquals('pending', $amendment->status);
    }
}
