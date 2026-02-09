<?php

namespace App\Domains\HR\Payroll\Actions;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Events\PayrollBatchFrozen;

/**
 * إجراء تجميد مسير الرواتب
 */
class FreezePayrollAction
{
    public function execute(PayrollBatch $batch, int $userId): PayrollBatch
    {
        // التحقق من إمكانية التجميد
        $batch->ensureEditable();

        // تجميد المسير
        $batch->freeze($userId);

        // قفل العقود المستخدمة (إذا كانت أول راتب لها)
        $contractIds = $batch->records()->pluck('contract_id')->unique();

        Contract::whereIn('id', $contractIds)
            ->where('is_locked', false)
            ->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => $userId,
            ]);

        // إطلاق الحدث (PR2.2)
        PayrollBatchFrozen::dispatch($batch->fresh());

        return $batch->fresh();
    }
}
