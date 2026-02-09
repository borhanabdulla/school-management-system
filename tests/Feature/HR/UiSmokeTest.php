<?php

namespace Tests\Feature\HR;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Livewire\HR\StaffAttendanceDashboard;
use App\Livewire\HR\Leave\LeaveRequestForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class UiSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_attendance_dashboard_renders(): void
    {
        $dayName = strtolower(substr(Carbon::now()->format('l'), 0, 3));
        $shift = WorkShift::create([
            'name' => 'اختبار',
            'season' => 'all',
            'start_time' => '08:00',
            'end_time' => '14:00',
            'grace_period_minutes' => 10,
            'working_days' => [$dayName],
            'works_on_holidays' => false,
            'is_active' => true,
        ]);

        Staff::factory()->create([
            'work_shift_id' => $shift->id,
        ]);

        Livewire::test(StaffAttendanceDashboard::class)
            ->assertStatus(200);
    }

    public function test_leave_request_form_renders(): void
    {
        \App\Domains\HR\Leave\Models\LeaveType::factory()->create();

        Livewire::test(LeaveRequestForm::class)
            ->assertStatus(200);
    }
}
