<?php

namespace App\Domains\Finance\Services;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class StudentFinancialClearanceService
{
    /**
     * حساب الرصيد المتبقي (المتأخرات) للطالب في سنة معينة.
     */
    public function getOutstandingBalance(int $studentId, int $academicYearId): float
    {
        // 1. جمع إجمالي الفواتير (Net) - المدفوع
        // Outstanding = Sum(Total Amount - Paid Amount) for non-cancelled invoices
        return Invoice::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function ($invoice) {
                // Ensure we don't have negative outstanding (if overpaid somehow, though strictly prevented now)
                return max(0, $invoice->total_amount - $invoice->paid_amount);
            });
    }

    /**
     * حساب الرصيد الافتتاحي (ما قبل سنة معينة)
     * PR-E1
     * 
     * Opening Balance = (Total Invoiced < Start Date) - (Total Paid < Start Date)
     */
    public function getPreviousBalanceBeforeYear(int $studentId, int $academicYearId): float
    {
        // نحتاج تاريخ بداية السنة الأكاديمية المستهدفة
        $targetYearStart = \App\Domains\Academic\AcademicYear\Models\AcademicYear::where('id', $academicYearId)->value('start_date');

        if (!$targetYearStart) {
            return 0.0;
        }

        // 1. إجمالي الفواتير الصادرة قبل بداية السنة
        $invoicedBeforeStats = Invoice::where('student_id', $studentId)
            ->whereDate('issue_date', '<', $targetYearStart)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount'); // total_amount is Net

        // 2. إجمالي المدفوعات المسجلة قبل بداية السنة (لأي فاتورة تابعة للطالب)
        // ملاحظة: نجمع المدفوعات التي تخص الطالب بغض النظر عن الفاتورة، لأننا نحسب رصيد عام
        $paidBeforeStats = Payment::whereHas('invoice', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
            ->where('status', PaymentStatus::Posted)
            ->whereDate('paid_at', '<', $targetYearStart)
            ->sum('amount');

        return (float) ($invoicedBeforeStats - $paidBeforeStats);
    }

    /**
     * التحقق من إخلاء الطرف المالي.
     * @return array{is_cleared: bool, reason: ?string}
     */
    public function checkClearance(int $studentId, int $academicYearId): array
    {
        // 1. التحقق من وجود استثناء إداري (Override)
        $override = DB::table('financial_clearance_overrides')
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if ($override) {
            return [
                'is_cleared' => true,
                'reason' => "تم منح استثناء إداري: {$override->reason}",
            ];
        }

        // 2. حساب المتأخرات
        $outstanding = $this->getOutstandingBalance($studentId, $academicYearId);

        if ($outstanding <= 0.0) {
            return [
                'is_cleared' => true,
                'reason' => null,
            ];
        }

        // 3. غير مخلّى
        return [
            'is_cleared' => false,
            'reason' => "توجد متأخرات مالية بقيمة " . number_format($outstanding, 2),
        ];
    }
    /**
     * التحقق من إخلاء الطرف لمجموعة من الطلاب (Batch Check).
     * @param array $studentIds
     * @param int $academicYearId
     * @return array<int, array{is_cleared: bool, reason: ?string}>
     */
    public function checkMany(array $studentIds, int $academicYearId): array
    {
        // 1. Load Overrides
        $overrides = DB::table('financial_clearance_overrides')
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $academicYearId)
            ->pluck('reason', 'student_id');

        // 2. Calculate Outstanding for all students via Invoice Aggregation
        // Query: Select student_id, sum(total_amount - paid_amount)
        $outstandingBalances = Invoice::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $academicYearId)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('student_id, SUM(CASE WHEN (total_amount - paid_amount) > 0 THEN (total_amount - paid_amount) ELSE 0 END) as balance')
            ->groupBy('student_id')
            ->pluck('balance', 'student_id');

        $results = [];
        foreach ($studentIds as $id) {
            // Priority 1: Override
            if ($overrides->has($id)) {
                $results[$id] = [
                    'is_cleared' => true,
                    'reason' => "تم منح استثناء إداري: {$overrides->get($id)}",
                ];
                continue;
            }

            // Priority 2: Outstanding Balance
            $balance = (float) ($outstandingBalances->get($id) ?? 0);

            if ($balance <= 0) {
                $results[$id] = [
                    'is_cleared' => true,
                    'reason' => null,
                ];
            } else {
                $results[$id] = [
                    'is_cleared' => false,
                    'reason' => "توجد متأخرات مالية بقيمة " . number_format($balance, 2),
                ];
            }
        }

        return $results;
    }
}
