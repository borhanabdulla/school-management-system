<?php

namespace App\Domains\Finance\Ledger\Listeners;

use App\Domains\Finance\Ledger\Data\LedgerEntryData;
use App\Domains\Finance\Ledger\Services\LedgerService;
use App\Domains\HR\Payroll\Events\PayrollBatchPaid;

/**
 * تسجيل صرف الرواتب في السجل المالي
 */
class RecordPayrollPayoutToLedger
{
    public function __construct(protected LedgerService $ledgerService)
    {
    }

    public function handle(PayrollBatchPaid $event): void
    {
        $batch = $event->batch;

        // بناء الـ DTO من الحدث
        $data = LedgerEntryData::forPayrollPayout(
            batch: $batch,
            createdBy: $batch->paid_by,
        );

        // التسجيل (Idempotent: لن يتكرر إذا كان موجوداً)
        $this->ledgerService->record($data);
    }
}
