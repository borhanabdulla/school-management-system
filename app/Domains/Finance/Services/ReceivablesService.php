<?php

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;

/**
 * ReceivablesService - حساب المستحقات (Outstanding) من الفواتير
 * 
 * ⚠️ هذه الخدمة منفصلة عن LedgerService:
 * - LedgerService = Cash (دخل/خرج فعلي)
 * - ReceivablesService = Outstanding (مستحقات لم تُحصّل)
 */
class ReceivablesService
{
    /**
     * إجمالي المستحقات لسنة أكاديمية محددة
     */
    public function getOutstandingForAcademicYear(int $academicYearId): float
    {
        return Invoice::where('academic_year_id', $academicYearId)
            ->outstanding()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as outstanding')
            ->value('outstanding') ?? 0;
    }

    /**
     * إجمالي المستحقات على مستوى النظام
     */
    public function getTotalOutstanding(): float
    {
        return Invoice::outstanding()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as outstanding')
            ->value('outstanding') ?? 0;
    }

    /**
     * ملخص المستحقات
     */
    public function getSummary(?int $academicYearId = null): array
    {
        $query = Invoice::query();

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $totals = $query->selectRaw('
            COALESCE(SUM(total_amount), 0) as total_billed,
            COALESCE(SUM(paid_amount), 0) as total_collected,
            COALESCE(SUM(total_amount - paid_amount), 0) as total_outstanding
        ')->first();

        return [
            'total_billed' => (float) ($totals->total_billed ?? 0),
            'total_collected' => (float) ($totals->total_collected ?? 0),
            'total_outstanding' => (float) ($totals->total_outstanding ?? 0),
        ];
    }
}
