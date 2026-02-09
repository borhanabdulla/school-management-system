<?php

namespace App\Domains\HR\Payroll\Actions;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Domains\HR\Payroll\Models\LoanInstallment;
use App\Domains\HR\Payroll\Models\Loan;
use App\Domains\HR\Payroll\Events\PayrollBatchApproved;
use App\Domains\HR\Payroll\Enums\LoanStatus;
use Illuminate\Support\Facades\DB;

/**
 * إجراء اعتماد مسير الرواتب
 */
class ApprovePayrollAction
{
    public function execute(PayrollBatch $batch, int $userId): PayrollBatch
    {
        return DB::transaction(function () use ($batch, $userId) {
            // 1. اعتماد المسير
            $batch->approve($userId);

            // 2. تحديث البنود المستهلكة (One-time Items)
            $contractItemIds = DB::table('payroll_items')
                ->join('payroll_records', 'payroll_items.payroll_record_id', '=', 'payroll_records.id')
                ->where('payroll_records.payroll_batch_id', $batch->id)
                ->where('payroll_items.source_type', ContractItem::class)
                ->pluck('payroll_items.source_id');

            if ($contractItemIds->isNotEmpty()) {
                ContractItem::whereIn('id', $contractItemIds)
                    ->where('is_one_time', true)
                    ->whereNull('consumed_at')
                    ->update(['consumed_at' => now()]);
            }

            // 3. تحديث أقساط السلف (Loan Installments)
            $installmentIds = DB::table('payroll_items')
                ->join('payroll_records', 'payroll_items.payroll_record_id', '=', 'payroll_records.id')
                ->where('payroll_records.payroll_batch_id', $batch->id)
                ->where('payroll_items.source_type', LoanInstallment::class)
                ->pluck('payroll_items.source_id');

            if ($installmentIds->isNotEmpty()) {
                LoanInstallment::whereIn('id', $installmentIds)
                    ->update([
                        'status' => 'paid',
                        'payroll_batch_id' => $batch->id
                    ]);

                $loans = Loan::whereHas('installments', function ($q) use ($installmentIds) {
                    $q->whereIn('id', $installmentIds);
                })->get();

                foreach ($loans as $loan) {
                    $paid = $loan->installments()->where('status', 'paid')->sum('amount');
                    $loan->update([
                        'paid_amount' => $paid,
                        'status' => $paid >= $loan->amount ? LoanStatus::Paid : LoanStatus::Approved
                    ]);
                }
            }

            // 4. إطلاق الحدث (PR2.2)
            PayrollBatchApproved::dispatch($batch->fresh());

            return $batch->fresh();
        });
    }
}
