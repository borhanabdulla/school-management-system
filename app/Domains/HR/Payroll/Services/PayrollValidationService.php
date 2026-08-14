<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Leave\Models\LeaveRequest;
use Carbon\Carbon;

/**
 * خدمة التحقق من صحة بيانات الرواتب قبل التوليد
 * 
 * PR-S1: Validation Service Pattern
 */
class PayrollValidationService
{
    /**
     * التحقق من صلاحية الفترة لإنشاء مسير رواتب
     * 
     * @return array ['errors' => [], 'warnings' => []]
     */
    public function validatePeriodForGeneration(int $year, int $month): array
    {
        $errors = [];
        $warnings = [];
        $start = Carbon::createFromDate($year, $month, 1);

        // 1️⃣ التحقق من وجود عقود نشطة
        $activeCount = Contract::active()->forPeriod($start)->count();
        if ($activeCount === 0) {
            $errors[] = [
                'type' => 'error',
                'code' => 'NO_ACTIVE_CONTRACTS',
                'message' => 'لا توجد عقود نشطة في هذه الفترة. لا يمكن إنشاء المسير.',
            ];
        }

        // 2️⃣ تحذير: موظفون براتب صفر
        $zeroSalaryCount = Contract::active()
            ->forPeriod($start)
            ->where('basic_salary', 0)
            ->count();

        if ($zeroSalaryCount > 0) {
            $warnings[] = [
                'type' => 'warning',
                'code' => 'ZERO_SALARY_CONTRACTS',
                'message' => "يوجد {$zeroSalaryCount} موظف براتب أساسي 0. يرجى المراجعة.",
            ];
        }

        // 3️⃣ تحذير: طلبات إجازة معلقة
        $pendingLeaves = LeaveRequest::pending()
            ->where(function ($q) use ($start) {
                $q->whereMonth('start_date', $start->month)
                    ->whereYear('start_date', $start->year);
            })
            ->count();

        if ($pendingLeaves > 0) {
            $warnings[] = [
                'type' => 'warning',
                'code' => 'PENDING_LEAVE_REQUESTS',
                'message' => "يوجد {$pendingLeaves} طلب إجازة معلق قد يؤثر على الرواتب.",
            ];
        }

        // 4️⃣ 🆕 التحقق من عدم وجود Overlapping Periods
        $existingBatch = PayrollBatch::where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existingBatch) {
            $errors[] = [
                'type' => 'error',
                'code' => 'DUPLICATE_PERIOD',
                'message' => "يوجد مسير رواتب موجود لنفس الفترة (ID: {$existingBatch->id}, الحالة: {$existingBatch->status->value})",
                'batch_id' => $existingBatch->id,
            ];
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'can_proceed' => empty($errors),
        ];
    }

    /**
     * التحقق من إمكانية الموافقة على مسير
     */
    public function canApproveBatch(PayrollBatch $batch): array
    {
        $errors = [];

        if ($batch->status !== \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Frozen) {
            $errors[] = [
                'type' => 'error',
                'code' => 'INVALID_STATUS',
                'message' => 'يجب أن يكون المسير في حالة "مجمد" للموافقة عليه.',
            ];
        }

        if ($batch->records()->count() === 0) {
            $errors[] = [
                'type' => 'error',
                'code' => 'NO_RECORDS',
                'message' => 'لا يوجد سجلات رواتب في المسير.',
            ];
        }

        return [
            'errors' => $errors,
            'can_proceed' => empty($errors),
        ];
    }

    /**
     * التحقق من إمكانية وضع علامة "مدفوع"
     */
    public function canMarkAsPaid(PayrollBatch $batch): array
    {
        $errors = [];

        if ($batch->status !== \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Approved) {
            $errors[] = [
                'type' => 'error',
                'code' => 'INVALID_STATUS',
                'message' => 'يجب أن يكون المسير "معتمد" لوضع علامة مدفوع.',
            ];
        }

        return [
            'errors' => $errors,
            'can_proceed' => empty($errors),
        ];
    }
}
