<?php

namespace App\Domains\HR\Staff\Observers;

use App\Domains\HR\Staff\Models\StaffAttendance;
use App\Domains\HR\Shared\Services\HRDashboardService;

/**
 * StaffAttendanceObserver - مراقب حضور الموظفين
 */
class StaffAttendanceObserver
{
    public function created(StaffAttendance $staffAttendance): void
    {
        $this->clearCache();
    }

    public function updated(StaffAttendance $staffAttendance): void
    {
        $this->clearCache();
    }

    public function deleted(StaffAttendance $staffAttendance): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        app(HRDashboardService::class)->clearCache();
    }
}
