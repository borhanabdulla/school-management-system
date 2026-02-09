<?php

namespace App\Domains\HR\Payroll\Observers;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Services\PayrollLookupService;
use Illuminate\Support\Facades\Cache;

/**
 * PayrollBatchObserver - مراقب مسيرات الرواتب
 * 
 * Purpose: إبطال الكاش تلقائياً عند تحديث حالة المسير
 */
class PayrollBatchObserver
{
    public function __construct(
        protected PayrollLookupService $lookupService
    ) {
    }

    /**
     * بعد التحديث (مثل Approve, Freeze, MarkAsPaid)
     */
    public function updated(PayrollBatch $batch): void
    {
        $this->lookupService->invalidateBatch(
            $batch->id,
            $batch->year,
            $batch->month
        );

        // إبطال كاش الإحصائيات العامة
        Cache::forget('payroll:pending_count');
        Cache::forget('payroll:dashboard_stats');
    }

    /**
     * بعد الحذف
     */
    public function deleted(PayrollBatch $batch): void
    {
        $this->lookupService->invalidateBatch(
            $batch->id,
            $batch->year,
            $batch->month
        );

        Cache::forget('payroll:pending_count');
    }
}
