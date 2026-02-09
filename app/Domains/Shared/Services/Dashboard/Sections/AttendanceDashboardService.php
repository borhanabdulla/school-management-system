<?php

namespace App\Domains\Shared\Services\Dashboard\Sections;

use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Calendar\Models\SchoolEvent;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardCache;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardQueries;
use App\Domains\Shared\Services\StatsHelper;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceDashboardService
{
    use HasDashboardCache;
    use HasDashboardQueries;

    public function attendanceTrend(array $filters): array
    {
        return $this->rememberDashboard($filters, 'attendance_trend', 3, function () use ($filters) {
            $rangeDays = (int) ($filters['range'] ?? 30);
            if ($rangeDays <= 0) {
                $rangeDays = 30;
            }

            $end = now()->startOfDay();
            $start = (clone $end)->subDays($rangeDays - 1);
            $previousStart = (clone $start)->subDays($rangeDays);
            $previousEnd = (clone $start)->subDay();

            $rows = $this->attendanceQuery($filters)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw(
                    'date, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present, COUNT(*) as total',
                    [AttendanceStatus::PRESENT->value]
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy(fn($row) => is_string($row->date) ? $row->date : $row->date->format('Y-m-d'));

            $trend = [];
            $maxTotal = 0;
            $maxRate = 0;

            foreach (CarbonPeriod::create($start, $end) as $date) {
                $key = $date->format('Y-m-d');
                $row = $rows->get($key);
                $present = (int) ($row->present ?? 0);
                $total = (int) ($row->total ?? 0);
                $rate = (int) StatsHelper::percentage($present, $total);

                $trend[] = [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('m/d'),
                    'present' => $present,
                    'total' => $total,
                    'rate' => $rate,
                ];

                $maxTotal = max($maxTotal, $total);
                $maxRate = max($maxRate, $rate);
            }

            $previousRows = $this->attendanceQuery($filters)
                ->whereBetween('date', [$previousStart->toDateString(), $previousEnd->toDateString()])
                ->selectRaw(
                    'date, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present, COUNT(*) as total',
                    [AttendanceStatus::PRESENT->value]
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy(fn($row) => is_string($row->date) ? $row->date : $row->date->format('Y-m-d'));

            $previousTrend = [];
            $previousMaxRate = 0;
            foreach (CarbonPeriod::create($previousStart, $previousEnd) as $date) {
                $key = $date->format('Y-m-d');
                $row = $previousRows->get($key);
                $present = (int) ($row->present ?? 0);
                $total = (int) ($row->total ?? 0);
                $rate = (int) StatsHelper::percentage($present, $total);

                $previousTrend[] = [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('m/d'),
                    'present' => $present,
                    'total' => $total,
                    'rate' => $rate,
                ];

                $previousMaxRate = max($previousMaxRate, $rate);
            }

            return [
                'items' => $trend,
                'max' => $maxTotal > 0 ? $maxTotal : 1,
                'avg_rate' => !empty($trend) ? round(collect($trend)->avg('rate')) : 0,
                'max_rate' => $maxRate,
                'previous_items' => $previousTrend,
                'previous_avg_rate' => !empty($previousTrend) ? round(collect($previousTrend)->avg('rate')) : 0,
                'previous_max_rate' => $previousMaxRate,
            ];
        });
    }

    public function attendanceHeatmap(array $filters): array
    {
        return $this->rememberDashboard($filters, 'attendance_heatmap', 5, function () use ($filters) {
            $rangeDays = (int) ($filters['range'] ?? 30);
            if ($rangeDays <= 0) {
                $rangeDays = 30;
            }

            $end = now()->startOfDay();
            $start = (clone $end)->subDays($rangeDays - 1);

            $rows = $this->attendanceQuery($filters)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw(
                    'date, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present, COUNT(*) as total',
                    [AttendanceStatus::PRESENT->value]
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy(fn($row) => is_string($row->date) ? $row->date : $row->date->format('Y-m-d'));

            $holidayDates = [];
            $events = SchoolEvent::query()
                ->where('is_holiday', true)
                ->inRange($start, $end)
                ->get();

            foreach ($events as $event) {
                $eventStart = $event->start_date?->copy()->startOfDay();
                $eventEnd = $event->end_date?->copy()->startOfDay();
                if (!$eventStart || !$eventEnd) {
                    continue;
                }
                $period = CarbonPeriod::create($eventStart, $eventEnd);
                foreach ($period as $date) {
                    $holidayDates[$date->format('Y-m-d')] = true;
                }
            }

            $days = [];
            foreach (CarbonPeriod::create($start, $end) as $date) {
                $key = $date->format('Y-m-d');
                $row = $rows->get($key);
                $present = (int) ($row->present ?? 0);
                $total = (int) ($row->total ?? 0);
                $rate = (int) StatsHelper::percentage($present, $total);
                $isHoliday = $holidayDates[$key] ?? false;
                $hasData = $total > 0;

                $days[] = [
                    'date' => $key,
                    'day' => $date->format('d'),
                    'weekday' => $date->format('D'),
                    'present' => $present,
                    'total' => $total,
                    'rate' => $rate,
                    'is_holiday' => $isHoliday,
                    'has_data' => $hasData,
                    'tone' => $this->heatmapTone($rate, $isHoliday, $hasData),
                ];
            }

            $weeks = array_chunk($days, 7);
            $weekLabels = ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'];

            return [
                'weeks' => $weeks,
                'labels' => $weekLabels,
                'start' => $start->format('Y/m/d'),
                'end' => $end->format('Y/m/d'),
            ];
        });
    }

    public function attendanceBreakdown(array $filters): array
    {
        return $this->rememberDashboard($filters, 'attendance_breakdown', 2, function () use ($filters) {
            $counts = $this->attendanceQuery($filters)
                ->whereDate('date', today())
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $statuses = [
                AttendanceStatus::PRESENT,
                AttendanceStatus::LATE,
                AttendanceStatus::ABSENT,
                AttendanceStatus::EXCUSED,
            ];

            $items = [];
            foreach ($statuses as $status) {
                $items[] = [
                    'label' => $status->label(),
                    'total' => (int) ($counts[$status->value] ?? 0),
                    'color' => $status->color(),
                ];
            }

            $max = !empty($items) ? max(array_column($items, 'total')) : 0;

            return [
                'items' => $items,
                'max' => $max > 0 ? $max : 1,
                'total' => array_sum(array_column($items, 'total')),
            ];
        }, extra: ['focus' => 'attendance']);
    }

    protected function heatmapTone(int $rate, bool $isHoliday, bool $hasData): string
    {
        if ($isHoliday) {
            return 'bg-sky-500/20 text-sky-300 border-sky-500/30';
        }
        if (!$hasData) {
            return 'bg-slate-200/60 text-slate-400 border-slate-200 dark:bg-slate-800/50 dark:text-slate-500 dark:border-slate-800';
        }
        if ($rate >= 95) {
            return 'bg-emerald-500/30 text-emerald-100 border-emerald-400/40';
        }
        if ($rate >= 90) {
            return 'bg-emerald-400/25 text-emerald-100 border-emerald-300/40';
        }
        if ($rate >= 80) {
            return 'bg-amber-400/25 text-amber-100 border-amber-300/40';
        }
        if ($rate >= 70) {
            return 'bg-orange-400/25 text-orange-100 border-orange-300/40';
        }

        return 'bg-red-500/25 text-red-100 border-red-400/40';
    }
}
