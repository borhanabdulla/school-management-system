<?php

namespace App\Domains\HR\Attendance\Services;

use App\Domains\HR\Staff\Enums\StaffAttendanceStatus;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Models\StaffAttendance;
use Carbon\Carbon;

class AttendanceSummaryService
{
    public function getStatsForPeriod(Staff $staff, Carbon $periodStart, Carbon $periodEnd): array
    {
        $records = StaffAttendance::where('staff_id', $staff->id)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();

        return [
            'absent_days' => $records->where('status', StaffAttendanceStatus::Absent)->count(),
            'late_days' => $records->where('status', StaffAttendanceStatus::Late)->count(),
            'total_late_minutes' => (int) $records->where('status', StaffAttendanceStatus::Late)->sum('delay_minutes'),
            'present_days' => $records->where('status', StaffAttendanceStatus::Present)->count(),
        ];
    }
}
