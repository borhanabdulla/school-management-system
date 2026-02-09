<?php

namespace App\Domains\HR\Leave\Observers;

use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Shared\Services\HRDashboardService;

/**
 * LeaveRequestObserver - مراقب طلبات الإجازة
 */
class LeaveRequestObserver
{
    public function created(LeaveRequest $leaveRequest): void
    {
        $this->clearHRDashboardCache();
    }

    public function updated(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->isDirty(['status', 'start_date', 'end_date'])) {
            $this->clearHRDashboardCache();
        }
    }

    public function deleted(LeaveRequest $leaveRequest): void
    {
        $this->clearHRDashboardCache();
    }

    protected function clearHRDashboardCache(): void
    {
        HRDashboardService::clearCache();
    }
}
