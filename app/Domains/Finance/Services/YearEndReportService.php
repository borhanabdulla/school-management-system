<?php

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\DiscountApplication;
use App\Domains\Finance\Models\Payment;
use Illuminate\Support\Facades\DB;
use App\Domains\Finance\Services\StudentFinancialClearanceService;

class YearEndReportService
{
    protected StudentFinancialClearanceService $clearanceService;

    public function __construct(StudentFinancialClearanceService $clearanceService)
    {
        $this->clearanceService = $clearanceService;
    }

    /**
     * Get Year Summary (Net based)
     */
    public function yearSummary(int $academicYearId): array
    {
        // 1. Total Invoiced Net (excluding cancelled)
        $invoicedNet = Invoice::where('academic_year_id', $academicYearId)
            ->notCancelled()
            ->sum('total_amount'); // total_amount IS net in our SST

        // 2. Total Discounts (for audit/display only - from discount applications)
        $discountApplications = DiscountApplication::whereHas('invoiceItem', function ($q) use ($academicYearId) {
            $q->whereHas('invoice', function ($q2) use ($academicYearId) {
                $q2->where('academic_year_id', $academicYearId)
                    ->notCancelled();
            });
        })->sum('applied_amount');

        // 3. Collected (Payments linked to invoices of this year)
        // Note: Payment date might be later, but it pays off a debt of this year.
        // We link payments via Invoice.
        $collected = Payment::whereHas('invoice', function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId);
            // Even if invoice is cancelled? Usually cancelled invoice has no payments or payments refunded.
            // But let's exclude cancelled invoices to be safe.
            $q->notCancelled();
        })->sum('amount');

        // 4. Outstanding
        $outstanding = $invoicedNet - $collected;

        return [
            'invoiced_net' => $invoicedNet,
            'total_discounts' => $discountApplications,
            'collected' => $collected,
            'outstanding' => $outstanding,
        ];
    }

    /**
     * Outstanding by Payer
     */
    public function outstandingByPayer(int $academicYearId): array
    {
        // Use SQL for aggregation for performance
        $results = Invoice::where('academic_year_id', $academicYearId)
            ->notCancelled()
            ->selectRaw('
                payer_guardian_id,
                SUM(total_amount) as total_net,
                SUM(paid_amount) as total_paid,
                SUM(CASE WHEN (total_amount - paid_amount) > 0 THEN (total_amount - paid_amount) ELSE 0 END) as outstanding,
                COUNT(DISTINCT student_id) as student_count
            ')
            ->groupBy('payer_guardian_id')
            ->get()
            ->map(function ($row) {
                return [
                    'payer_guardian_id' => $row->payer_guardian_id,
                    'invoiced_net' => (float) $row->total_net,
                    'collected' => (float) $row->total_paid,
                    'outstanding' => (float) $row->outstanding,
                    'student_count' => (int) $row->student_count,
                ];
            })
            ->toArray();

        return $results;
    }

    /**
     * Outstanding by Student
     */
    public function outstandingByStudent(int $academicYearId): array
    {
        $results = Invoice::where('academic_year_id', $academicYearId)
            ->notCancelled()
            ->selectRaw('
                student_id,
                SUM(total_amount) as total_net,
                SUM(paid_amount) as total_paid,
                SUM(CASE WHEN (total_amount - paid_amount) > 0 THEN (total_amount - paid_amount) ELSE 0 END) as outstanding
            ')
            ->groupBy('student_id')
            ->get()
            ->map(function ($row) {
                return [
                    'student_id' => $row->student_id,
                    'invoiced_net' => (float) $row->total_net,
                    'collected' => (float) $row->total_paid,
                    'outstanding' => (float) $row->outstanding,
                ];
            })
            ->toArray();

        return $results;
    }

    /**
     * Blocked Students List
     */
    public function blockedStudents(int $academicYearId): array
    {
        // Get all students with outstanding > 0
        $studentsWithDebt = Invoice::where('academic_year_id', $academicYearId)
            ->notCancelled()
            ->selectRaw('student_id, SUM(CASE WHEN (total_amount - paid_amount) > 0 THEN (total_amount - paid_amount) ELSE 0 END) as balance')
            ->groupBy('student_id')
            ->having('balance', '>', 0)
            ->pluck('balance', 'student_id');

        // Get overrides
        $overrides = DB::table('financial_clearance_overrides')
            ->where('academic_year_id', $academicYearId)
            ->pluck('reason', 'student_id');

        $blocked = [];

        foreach ($studentsWithDebt as $studentId => $balance) {
            if (!$overrides->has($studentId)) {
                $blocked[] = [
                    'student_id' => $studentId,
                    'outstanding' => (float) $balance,
                    'reason' => 'Financial Arrears',
                ];
            }
        }

        return $blocked;
    }
}
