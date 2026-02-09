<?php

namespace Tests\Feature\HR;

use App\Domains\HR\Staff\Actions\DeleteStaffAction;
use App\Domains\HR\Staff\Exceptions\StaffNotDeletableException;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Models\StaffAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StaffBasicsTest extends TestCase
{
    use RefreshDatabase;

    public function test_joining_date_casts_to_date(): void
    {
        $date = '2025-01-15';
        $staff = Staff::factory()->create(['joining_date' => $date]);

        $fresh = $staff->fresh();
        $this->assertInstanceOf(Carbon::class, $fresh->joining_date);
        $this->assertSame($date, $fresh->joining_date->format('Y-m-d'));
    }

    public function test_delete_staff_with_attendance_is_blocked(): void
    {
        $staff = Staff::factory()->create();
        StaffAttendance::factory()->create(['staff_id' => $staff->id]);

        $this->expectException(StaffNotDeletableException::class);
        app(DeleteStaffAction::class)->execute($staff);
    }
}
