<?php

namespace App\Domains\HR\Payroll\Actions;

use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Models\PayrollRecord;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Exceptions\DuplicatePayrollException;
use App\Domains\HR\Payroll\Services\PayrollCalculationService;
use Illuminate\Support\Facades\DB;

/**
 * إجراء توليد مسير رواتب جديد
 */
class GeneratePayrollAction
{
    public function __construct(
        private PayrollCalculationService $calculationService
    ) {
    }

    /**
     * توليد مسير رواتب لفترة محددة
     */
    public function execute(PayrollGenerationData $data, int $userId): PayrollBatch
    {
        // 1. التحقق من عدم وجود مسير لنفس الفترة
        $exists = PayrollBatch::where('year', $data->year)
            ->where('month', $data->month)
            ->exists();

        if ($exists) {
            throw new DuplicatePayrollException();
        }

        return DB::transaction(function () use ($data, $userId) {
            // استنتاج السنة الأكاديمية
            $academicYearId = \App\Domains\Academic\AcademicYear\Models\AcademicYear::query()
                ->whereDate('start_date', '<=', $data->period_start)
                ->whereDate('end_date', '>=', $data->period_start)
                ->value('id');

            // ✅ PR-CF4: Validate academic_year_id exists
            if (!$academicYearId) {
                throw new \App\Domains\HR\Payroll\Exceptions\AcademicYearNotFoundException(
                    "لا يمكن تحديد السنة الأكاديمية لفترة الرواتب {$data->period_start->format('Y-m')}"
                );
            }

            // 2. إنشاء المسير
            $batch = PayrollBatch::create([
                'name' => $data->name ?? "مسير رواتب " . $data->period_start->translatedFormat('F Y'),
                'academic_year_id' => $academicYearId,
                'period_start' => $data->period_start,
                'period_end' => $data->period_end,
                'year' => $data->year,
                'month' => $data->month,
                'status' => 'draft',
                'generated_by' => $userId,
                'notes' => $data->notes,
            ]);

            // 3. جلب الموظفين الذين لديهم عقود نشطة في الفترة
            // نستخدم distinct staff_id لأن الموظف قد يكون له أكثر من عقد (Split Month)
            $staffIds = Contract::active()
                ->forPeriod($data->period_start)
                ->distinct()
                ->pluck('staff_id');

            $staffMembers = \App\Domains\HR\Staff\Models\Staff::whereIn('id', $staffIds)->get();

            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;
            $recordsCount = 0;

            // 4. إنشاء سجل لكل موظف
            foreach ($staffMembers as $staff) {
                // حساب التفاصيل (يستخدم Orchestrator Logic)
                $calculation = $this->calculationService->calculateForStaff(
                    $staff,
                    $data->period_start,
                    $data->period_end
                );

                if (empty($calculation))
                    continue;

                // إنشاء السجل
                $record = PayrollRecord::create([
                    'payroll_batch_id' => $batch->id,
                    'staff_id' => $staff->id,
                    'contract_id' => $calculation['meta']['latest_contract_id'], // نربط بآخر عقد كمرجع
                    'basic_salary' => $calculation['meta']['basic_salary'],
                    'working_days' => $calculation['totals']['working_days'],
                    'days_worked' => $calculation['totals']['days_worked'],
                    'days_absent' => $calculation['totals']['days_absent'],
                    'days_late' => $calculation['totals']['days_late'],
                    'gross_earnings' => $calculation['totals']['gross_earnings'],
                    'total_deductions' => $calculation['totals']['total_deductions'],
                    'net_payable' => $calculation['totals']['net_payable'],
                ]);

                // إضافة البنود
                foreach ($calculation['earnings'] as $earning) {
                    $record->items()->create($earning);
                }

                foreach ($calculation['deductions'] as $deduction) {
                    $record->items()->create($deduction);
                }

                $totalGross += $calculation['totals']['gross_earnings'];
                $totalDeductions += $calculation['totals']['total_deductions'];
                $totalNet += $calculation['totals']['net_payable'];
                $recordsCount++;
            }

            // 5. تحديث إجماليات المسير
            $batch->update([
                'total_gross' => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net' => $totalNet,
                'employees_count' => $recordsCount,
            ]);

            // 6. إطلاق الحدث (PR2.2)
            \App\Domains\HR\Payroll\Events\PayrollBatchGenerated::dispatch($batch->fresh());

            return $batch->fresh();
        });
    }
}
