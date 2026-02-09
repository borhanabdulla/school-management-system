<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use Carbon\Carbon;

class PayrollPeriodLockService
{
    public function getLockingBatchForRange(Carbon $start, Carbon $end): ?PayrollBatch
    {
        return PayrollBatch::query()
            ->whereIn('status', [
                PayrollBatchStatus::Frozen,
                PayrollBatchStatus::Approved,
                PayrollBatchStatus::Paid,
            ])
            ->where('period_start', '<=', $end->toDateString())
            ->where('period_end', '>=', $start->toDateString())
            ->orderBy('period_start')
            ->first();
    }

    public function isLockedForDate(Carbon $date): bool
    {
        return $this->getLockingBatchForRange($date, $date) !== null;
    }

    public function isLockedForRange(Carbon $start, Carbon $end): bool
    {
        return $this->getLockingBatchForRange($start, $end) !== null;
    }
}
