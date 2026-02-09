<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Support\Facades\DB;

/**
 * خدمة تقارير الرواتب
 * PR-C2
 */
class PayrollReportService
{
    /**
     * تقرير الرواتب حسب سنة الاستحقاق الأكاديمية
     * (بغض النظر عن تاريخ الدفع)
     */
    public function getPayrollByAcademicYear(int $academicYearId): array
    {
        return PayrollBatch::where('academic_year_id', $academicYearId)
            ->where('status', '!=', PayrollBatchStatus::Draft) // نستبعد المسودات
            ->selectRaw('
                academic_year_id,
                SUM(total_gross) as total_gross,
                SUM(total_deductions) as total_deductions,
                SUM(total_net) as total_net,
                COUNT(id) as batch_count
            ')
            ->groupBy('academic_year_id')
            ->first()
                ?->toArray() ?? [
            'academic_year_id' => $academicYearId,
            'total_gross' => 0.0,
            'total_deductions' => 0.0,
            'total_net' => 0.0,
            'batch_count' => 0,
        ];
    }

    /**
     * تقرير التدفق النقدي للرواتب (Cashflow)
     * يعتمد على تاريخ الصرف الفعلي (paid_at)
     */
    public function getCashflowReport(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        return PayrollBatch::where('status', PayrollBatchStatus::Paid)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(paid_at) as payment_date,
                SUM(total_net) as total_paid_out
            ')
            ->groupBy('payment_date')
            ->orderBy('payment_date')
            ->get()
            ->toArray();
    }

    /**
     * PR-CF3: توزيع التكاليف (راتب أساسي، بدلات، خصومات)
     * 
     * Purpose: تجنب DB::table في Livewire
     */
    public function getCostDistribution(): array
    {
        $basicTotal = \App\Domains\HR\Payroll\Models\Contract::where('status', 'active')
            ->sum('basic_salary');

        // استخدام Eloquent Relationships بدلاً من DB::table
        $allowancesTotal = \App\Domains\HR\Payroll\Models\ContractItem::whereHas('contract', function ($q) {
            $q->where('status', 'active');
        })
            ->where('type', 'allowance')
            ->where('is_one_time', false) // فقط البدلات الشهرية
            ->sum('amount');

        return [
            'basic' => (float) $basicTotal,
            'allowances' => (float) $allowancesTotal,
            'deductions' => 0, // يمكن حسابها من آخر batch
        ];
    }
}
