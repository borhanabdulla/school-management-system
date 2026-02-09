<?php

namespace App\Domains\HR\Payroll\Listeners;

use App\Domains\HR\Payroll\Events\PayrollBatchGenerated;
use App\Domains\HR\Payroll\Events\PayrollBatchFrozen;
use App\Domains\HR\Payroll\Events\PayrollBatchApproved;
use App\Domains\HR\Payroll\Events\PayrollBatchPaid;
use App\Domains\HR\Payroll\Services\PayrollCacheService;

class PayrollCacheInvalidationListener
{
    public function __construct(protected PayrollCacheService $cacheService)
    {
    }

    public function handle(object $event): void
    {
        // التحقق من نوع الحدث وأن لديه batch
        if (!property_exists($event, 'batch')) {
            return;
        }

        if (
            $event instanceof PayrollBatchGenerated ||
            $event instanceof PayrollBatchFrozen ||
            $event instanceof PayrollBatchApproved ||
            $event instanceof PayrollBatchPaid
        ) {

            $this->cacheService->invalidateBatch($event->batch);
        }
    }
}
