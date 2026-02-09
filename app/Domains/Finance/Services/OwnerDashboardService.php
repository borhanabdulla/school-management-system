<?php

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Ledger\Models\LedgerEntry;
use App\Domains\Finance\Ledger\Services\LedgerService;
use Carbon\Carbon;

/**
 * OwnerDashboardService - تجميع بيانات لوحة المالك
 * 
 * يجمع البيانات من:
 * - LedgerService → Cash totals
 * - ReceivablesService → Outstanding
 * - LedgerEntry → Recent transactions
 */
class OwnerDashboardService
{
    public function __construct(
        protected LedgerService $ledgerService,
        protected ReceivablesService $receivablesService
    ) {
    }

    /**
     * الحصول على ملخص شامل للوحة المالك
     */
    public function getDashboardData(string $period = 'month', ?int $academicYearId = null): array
    {
        [$start, $end] = $this->getPeriodDates($period);

        // Cash from Ledger
        $cashSummary = $this->ledgerService->getCashSummaryForPeriod($start, $end);

        // Receivables from Invoices (All Years - Primary Metric)
        $receivablesAll = $this->receivablesService->getSummary(null);

        // Receivables for Specific Year (Optional)
        $receivablesYear = $academicYearId ? $this->receivablesService->getSummary($academicYearId) : null;

        // Recent Transactions
        $recentTransactions = LedgerEntry::posted()
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($entry) => [
                'id' => $entry->id,
                'date' => $entry->entry_date->format('Y-m-d'),
                'direction' => $entry->direction->value,
                'amount' => (float) $entry->amount,
                'category' => $entry->category->value,
                'notes' => $entry->notes,
            ]);

        return [
            'period' => [
                'label' => $this->getPeriodLabel($period),
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'cash' => $cashSummary,
            'receivables_all' => $receivablesAll,
            'receivables_year' => $receivablesYear,
            'recent_transactions' => $recentTransactions,
        ];
    }

    protected function getPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [
                Carbon::today()->startOfDay(),
                Carbon::today()->endOfDay(),
            ],
            'month' => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
            'year' => [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ],
            default => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
        };
    }

    protected function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'اليوم',
            'month' => 'هذا الشهر',
            'year' => 'هذه السنة',
            default => 'هذا الشهر',
        };
    }
}
