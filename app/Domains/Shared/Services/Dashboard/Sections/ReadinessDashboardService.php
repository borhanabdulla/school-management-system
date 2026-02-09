<?php

namespace App\Domains\Shared\Services\Dashboard\Sections;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Services\ReadinessService;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardCache;
use App\Domains\Shared\Services\StatsHelper;

class ReadinessDashboardService
{
    use HasDashboardCache;

    public function __construct(
        protected ReadinessService $readinessService
    ) {
    }

    public function readinessSummary(?AcademicYear $selectedYear, array $filters): array
    {
        if (!$selectedYear) {
            return [];
        }

        return $this->rememberDashboard($filters, 'readiness_summary', 3, function () use ($selectedYear) {
            return $this->readinessService->getSummary($selectedYear);
        });
    }

    public function readinessPareto(array $summary, array $filters): array
    {
        return $this->rememberDashboard($filters, 'readiness_pareto', 3, function () use ($summary) {
            $items = collect($summary['items'] ?? [])
                ->filter(fn($item) => ($item['has_issues'] ?? false) && ($item['count'] ?? 0) > 0)
                ->sortByDesc('count')
                ->values();

            $total = $items->sum('count');
            $max = $items->max('count') ?? 1;
            $cumulative = 0;

            $pared = $items->map(function ($item) use (&$cumulative, $total) {
                $count = (int) ($item['count'] ?? 0);
                $cumulative += $count;
                $percent = (int) StatsHelper::percentage($count, $total);
                $cumulativePercent = (int) StatsHelper::percentage($cumulative, $total);

                return [
                    'label' => $item['label'] ?? 'غير محدد',
                    'count' => $count,
                    'percent' => $percent,
                    'cumulative' => $cumulativePercent,
                    'route' => $item['route'] ?? null,
                    'route_params' => $item['route_params'] ?? [],
                ];
            });

            return [
                'items' => $pared->toArray(),
                'total' => $total,
                'max' => $max > 0 ? $max : 1,
            ];
        });
    }
}
