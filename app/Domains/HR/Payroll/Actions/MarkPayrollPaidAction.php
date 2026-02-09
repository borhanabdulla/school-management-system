<?php

namespace App\Domains\HR\Payroll\Actions;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Enums\PayoutMethod;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Events\PayrollBatchPaid;
use App\Domains\HR\Payroll\Exceptions\InvalidWorkflowStateException;
use Illuminate\Support\Facades\DB;

/**
 * إجراء صرف مسير الرواتب (Cash-Out)
 * 
 * PR6: Concurrency Hardening
 * - lockForUpdate لمنع race condition
 * - Idempotency: إذا المسير paid بالفعل → return early
 */
class MarkPayrollPaidAction
{
    /**
     * @param PayrollBatch $batch المسير (سيُعاد جلبه بـ lock)
     * @param int $paidByUserId المستخدم الذي صرف
     * @param PayoutMethod|null $method طريقة الصرف
     * @param string|null $reference مرجع الصرف
     * @return PayrollBatch
     */
    public function execute(
        PayrollBatch $batch,
        int $paidByUserId,
        ?PayoutMethod $method = null,
        ?string $reference = null
    ): PayrollBatch {
        return DB::transaction(function () use ($batch, $paidByUserId, $method, $reference) {
            // PR6.1: جلب المسير مع Lock لمنع concurrent updates
            $lockedBatch = PayrollBatch::lockForUpdate()->find($batch->id);

            if (!$lockedBatch) {
                throw new InvalidWorkflowStateException('المسير غير موجود');
            }

            // PR6.1: Idempotency - إذا مصروف بالفعل، نرجع بدون خطأ
            if ($lockedBatch->status === PayrollBatchStatus::Paid) {
                // Already paid, return without error (idempotent)
                return $lockedBatch;
            }

            // تحديث الحالة وتوثيق الصرف
            $lockedBatch->markAsPaid($paidByUserId, $method, $reference);

            // إطلاق حدث الصرف (Ledger يسجل عبر Listener)
            PayrollBatchPaid::dispatch($lockedBatch);

            return $lockedBatch->fresh();
        });
    }
}
