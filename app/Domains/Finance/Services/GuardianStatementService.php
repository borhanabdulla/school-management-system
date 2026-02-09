<?php

namespace App\Domains\Finance\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Enums\PaymentStatus;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;

/**
 * GuardianStatementService - خدمة كشف حساب ولي الأمر
 * PR-E2
 */
class GuardianStatementService
{
    /**
     * جلب كشف حساب تفصيلي لسنة أكاديمية محددة
     */
    public function getStatement(int $guardianId, int $academicYearId): array
    {
        $year = AcademicYear::findOrFail($academicYearId);
        $start = $year->start_date;
        $end = $year->end_date;

        // 1. الرصيد الافتتاحي (Opening Balance)
        // الفواتير السابقة - المدفوعات السابقة
        $prevInvoiced = Invoice::where('payer_guardian_id', $guardianId)
            ->whereDate('issue_date', '<', $start)
            ->where('status', '!=', \App\Domains\Finance\Enums\InvoiceStatus::Cancelled)
            ->sum('total_amount');

        $prevPaid = Payment::where('guardian_id', $guardianId)
            ->where('status', PaymentStatus::Posted)
            ->whereDate('paid_at', '<', $start)
            ->sum('amount');

        $openingBalance = $prevInvoiced - $prevPaid;

        // 2. الحركات خلال السنة (Movements)
        // أ) الفواتير (مدين / Debit)
        $invoices = Invoice::where('payer_guardian_id', $guardianId)
            ->whereBetween('issue_date', [$start, $end])
            ->where('status', '!=', \App\Domains\Finance\Enums\InvoiceStatus::Cancelled)
            ->get()
            ->map(function ($inv) {
                return [
                    'date' => $inv->issue_date,
                    'type' => 'invoice',
                    'description' => "فاتورة #{$inv->invoice_number} - " . ($inv->student->name ?? 'طالب'),
                    'debit' => $inv->total_amount,
                    'credit' => 0,
                    'ref_id' => $inv->id,
                ];
            });

        // ب) المدفوعات (دائن / Credit)
        $payments = Payment::where('guardian_id', $guardianId)
            ->where('status', PaymentStatus::Posted)
            ->whereBetween('paid_at', [$start, $end])
            ->get()
            ->map(function ($pay) {
                return [
                    'date' => $pay->paid_at,
                    'type' => 'payment',
                    'description' => "دفعة #{$pay->id} " . ($pay->method->label() ?? ''),
                    'debit' => 0,
                    'credit' => $pay->amount,
                    'ref_id' => $pay->id,
                ];
            });

        // دمج وفرز الحركات
        $movements = $invoices->merge($payments)
            ->sortBy(fn($m) => $m['date']->timestamp)
            ->values();

        // 3. حساب الأرصدة التراكمية (Running Balance)
        $runningBalance = $openingBalance;
        $formattedMovements = [];

        foreach ($movements as $mov) {
            $runningBalance += ($mov['debit'] - $mov['credit']);

            $formattedMovements[] = array_merge($mov, [
                'balance' => $runningBalance,
                'date' => $mov['date']->format('Y-m-d'),
            ]);
        }

        return [
            'guardian_id' => $guardianId,
            'academic_year_id' => $academicYearId,
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'opening_balance' => $openingBalance,
            'movements' => $formattedMovements,
            'closing_balance' => $runningBalance,
        ];
    }
}
