<?php

namespace App\Domains\Shared\Services\Dashboard\Sections;

use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardCache;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardQueries;
use App\Domains\Shared\Services\StatsHelper;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class FinanceDashboardService
{
    use HasDashboardCache;
    use HasDashboardQueries;

    public function financeTrend(array $filters): array
    {
        return $this->rememberDashboard($filters, 'finance_trend', 5, function () use ($filters) {
            $rangeDays = (int) ($filters['range'] ?? 30);
            if ($rangeDays <= 0) {
                $rangeDays = 30;
            }

            $end = now()->startOfDay();
            $start = (clone $end)->subDays($rangeDays - 1);
            $previousStart = (clone $start)->subDays($rangeDays);
            $previousEnd = (clone $start)->subDay();

            $rows = $this->invoiceQuery($filters)
                ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('issue_date as date, SUM(paid_amount) as total')
                ->groupBy('issue_date')
                ->orderBy('issue_date')
                ->get()
                ->keyBy(fn($row) => is_string($row->date) ? $row->date : $row->date->format('Y-m-d'));

            $trend = [];
            $maxTotal = 0;
            $sum = 0;
            foreach (CarbonPeriod::create($start, $end) as $date) {
                $key = $date->format('Y-m-d');
                $row = $rows->get($key);
                $total = (float) ($row->total ?? 0);
                $sum += $total;

                $trend[] = [
                    'label' => $date->format('m/d'),
                    'total' => round($total, 2),
                ];

                $maxTotal = max($maxTotal, $total);
            }

            $previousRows = $this->invoiceQuery($filters)
                ->whereBetween('issue_date', [$previousStart->toDateString(), $previousEnd->toDateString()])
                ->selectRaw('issue_date as date, SUM(paid_amount) as total')
                ->groupBy('issue_date')
                ->orderBy('issue_date')
                ->get()
                ->keyBy(fn($row) => is_string($row->date) ? $row->date : $row->date->format('Y-m-d'));

            $previousTrend = [];
            $previousMaxTotal = 0;
            $previousTotal = 0;
            foreach (CarbonPeriod::create($previousStart, $previousEnd) as $date) {
                $key = $date->format('Y-m-d');
                $row = $previousRows->get($key);
                $total = (float) ($row->total ?? 0);
                $previousTotal += $total;

                $previousTrend[] = [
                    'label' => $date->format('m/d'),
                    'total' => round($total, 2),
                ];

                $previousMaxTotal = max($previousMaxTotal, $total);
            }

            $delta = $sum - $previousTotal;
            $direction = $delta === 0 ? 'flat' : ($delta > 0 ? 'up' : 'down');
            $deltaPercent = $previousTotal > 0
                ? (int) StatsHelper::percentage(abs($delta), $previousTotal)
                : ($sum > 0 ? 100 : 0);

            return [
                'items' => $trend,
                'max' => $maxTotal > 0 ? $maxTotal : 1,
                'sum' => round($sum, 2),
                'delta' => round($delta, 2),
                'direction' => $direction,
                'delta_percent' => $deltaPercent,
                'previous_items' => $previousTrend,
                'previous_max' => $previousMaxTotal,
            ];
        });
    }

    public function financeSummary(array $filters): array
    {
        return $this->rememberDashboard($filters, 'finance_summary', 3, function () use ($filters) {
            $unpaidStatuses = InvoiceStatus::unpaidValues();
            $placeholders = implode(',', array_fill(0, count($unpaidStatuses), '?'));

            $row = $this->invoiceQuery($filters)
                ->selectRaw(
                    "COUNT(*) as invoice_count,
                    SUM(total_amount) as total,
                    SUM(paid_amount) as paid,
                    SUM(CASE WHEN status IN ({$placeholders}) THEN 1 ELSE 0 END) as outstanding_count",
                    $unpaidStatuses
                )
                ->first();

            $total = (float) ($row->total ?? 0);
            $paid = (float) ($row->paid ?? 0);
            $outstanding = max(0, $total - $paid);

            return [
                'total' => $total,
                'paid' => $paid,
                'outstanding' => $outstanding,
                'invoice_count' => (int) ($row->invoice_count ?? 0),
                'outstanding_count' => (int) ($row->outstanding_count ?? 0),
                'paid_ratio' => (int) StatsHelper::percentage($paid, $total),
            ];
        });
    }

    public function financeAgingBuckets(array $filters): array
    {
        return $this->rememberDashboard($filters, 'finance_aging', 5, function () use ($filters) {
            $daysExpr = $this->daysOverdueExpression();

            $row = $this->invoiceQuery($filters)
                ->outstanding()
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->selectRaw(
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as c0_30, " .
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as c31_60, " .
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as c61_90, " .
                    "SUM(CASE WHEN {$daysExpr} > 90 THEN 1 ELSE 0 END) as c90_plus, " .
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 1 AND 30 THEN (total_amount - paid_amount) ELSE 0 END) as a0_30, " .
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 31 AND 60 THEN (total_amount - paid_amount) ELSE 0 END) as a31_60, " .
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 61 AND 90 THEN (total_amount - paid_amount) ELSE 0 END) as a61_90, " .
                    "SUM(CASE WHEN {$daysExpr} > 90 THEN (total_amount - paid_amount) ELSE 0 END) as a90_plus"
                )
                ->first();

            $buckets = [
                ['label' => '0-30 يوم', 'count' => (int) ($row->c0_30 ?? 0), 'amount' => (float) ($row->a0_30 ?? 0)],
                ['label' => '31-60 يوم', 'count' => (int) ($row->c31_60 ?? 0), 'amount' => (float) ($row->a31_60 ?? 0)],
                ['label' => '61-90 يوم', 'count' => (int) ($row->c61_90 ?? 0), 'amount' => (float) ($row->a61_90 ?? 0)],
                ['label' => '+90 يوم', 'count' => (int) ($row->c90_plus ?? 0), 'amount' => (float) ($row->a90_plus ?? 0)],
            ];

            $maxCount = !empty($buckets) ? max(array_column($buckets, 'count')) : 0;

            return [
                'items' => $buckets,
                'max' => $maxCount > 0 ? $maxCount : 1,
            ];
        });
    }

    public function invoiceBreakdown(array $filters): array
    {
        return $this->rememberDashboard($filters, 'invoice_breakdown', 4, function () use ($filters) {
            $counts = $this->invoiceQuery($filters)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $items = [];
            foreach (InvoiceStatus::cases() as $status) {
                $items[] = [
                    'status' => $status->value,
                    'label' => $status->label(),
                    'total' => (int) ($counts[$status->value] ?? 0),
                    'color' => $status->color(),
                ];
            }

            $max = !empty($items) ? max(array_column($items, 'total')) : 0;

            return [
                'items' => $items,
                'max' => $max > 0 ? $max : 1,
            ];
        }, extra: ['focus' => 'finance']);
    }

    public function invoiceStatusSummary(InvoiceStatus $status, array $filters): array
    {
        return $this->rememberDashboard($filters, 'invoice_status_summary:' . $status->value, 3, function () use ($filters, $status) {
            $row = $this->invoiceQuery($filters)
                ->where('status', $status->value)
                ->selectRaw('COUNT(*) as total, SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount')
                ->first();

            $totalAmount = (float) ($row->total_amount ?? 0);
            $paidAmount = (float) ($row->paid_amount ?? 0);
            $outstanding = max($totalAmount - $paidAmount, 0);
            $paidRatio = (int) StatsHelper::percentage($paidAmount, $totalAmount);

            return [
                'total' => $totalAmount,
                'paid' => $paidAmount,
                'outstanding' => $outstanding,
                'paid_ratio' => $paidRatio,
                'count' => (int) ($row->total ?? 0),
            ];
        });
    }
}
