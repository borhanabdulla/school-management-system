<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Models\PayrollRecord;
use App\Domains\HR\Payroll\Enums\PayrollItemType;
use Illuminate\Support\Facades\DB;

class PayrollTotalsService
{
    /**
     * إعادة حساب مجاميع سجل الرواتب بناءً على بنوده
     */
    public function recalculateRecord(PayrollRecord $record): void
    {
        // 1. حساب المجموع من البنود فقط
        $earnings = $record->items()
            ->where('type', PayrollItemType::Earning)
            ->sum('amount');

        $deductions = $record->items()
            ->where('type', PayrollItemType::Deduction)
            ->sum('amount');

        // 2. تحديث السجل
        // ملاحظة: نعتمد على items فقط، manual_adjustment يجب أن يكون item أيضاً أو يُلغى.
        // ولكن حسب الموديل الحالي يوجد manual_adjustment.
        // الأفضل: أن كل شيء يكون item. سأفترض حالياً أن manual_adjustment موجود وسأستخدمه،
        // ولكن يجب التفكير في نقله ليكون item لاحقاً لتوحيد المصدر تماماً.

        $netPayable = $earnings - $deductions;
        if (isset($record->manual_adjustment)) {
            $netPayable += $record->manual_adjustment;
        }

        $record->update([
            'gross_earnings' => $earnings,
            'total_deductions' => $deductions,
            'net_payable' => max(0, $netPayable), // لا رواتب بالسالب
        ]);

        // 3. إعادة حساب الباتش لضمان التناسق
        if ($record->payroll_batch_id) {
            // يمكن تأجيلها أو عملها هنا. الأفضل استدعاؤها صراحة عند الحاجة لتجنب N+1 updates
            // لكن لضمان الصحة دائماً، سنحدثها.
            $this->recalculateBatch($record->batch);
        }
    }

    /**
     * إعادة حساب مجاميع الباتش بناءً على سجلاته
     */
    public function recalculateBatch(PayrollBatch $batch): void
    {
        $totals = $batch->records()
            ->selectRaw('
                count(*) as count_records,
                sum(gross_earnings) as sum_gross,
                sum(total_deductions) as sum_deductions,
                sum(net_payable) as sum_net
            ')
            ->first();

        $batch->update([
            'employees_count' => (int) ($totals->count_records ?? 0),
            'total_gross' => (float) ($totals->sum_gross ?? 0),
            'total_deductions' => (float) ($totals->sum_deductions ?? 0),
            'total_net' => (float) ($totals->sum_net ?? 0),
        ]);
    }
}
