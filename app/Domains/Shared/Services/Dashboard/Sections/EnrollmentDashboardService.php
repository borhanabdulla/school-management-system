<?php

namespace App\Domains\Shared\Services\Dashboard\Sections;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Shared\Enums\Gender;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardCache;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardQueries;
use App\Domains\Shared\Services\StatsHelper;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class EnrollmentDashboardService
{
    use HasDashboardCache;
    use HasDashboardQueries;

    public function gradeDistribution(array $filters): array
    {
        return $this->rememberDashboard($filters, 'grade_distribution', 5, function () use ($filters) {
            $counts = $this->enrollmentQuery($filters)
                ->select('grade_id', DB::raw('COUNT(*) as total'))
                ->groupBy('grade_id')
                ->pluck('total', 'grade_id');

            $grades = Grade::query()->orderBy('level_order')->get();

            $distribution = $grades->map(function ($grade) use ($counts) {
                return [
                    'name' => $grade->name,
                    'total' => (int) ($counts[$grade->id] ?? 0),
                ];
            })->toArray();

            $max = !empty($distribution) ? max(array_column($distribution, 'total')) : 0;

            return [
                'items' => $distribution,
                'max' => $max > 0 ? $max : 1,
            ];
        });
    }

    public function enrollmentTrend(array $filters): array
    {
        return $this->rememberDashboard($filters, 'enrollment_trend', 5, function () use ($filters) {
            $rangeDays = (int) ($filters['range'] ?? 30);
            if ($rangeDays <= 0) {
                $rangeDays = 30;
            }

            $end = now()->startOfDay();
            $start = (clone $end)->subDays($rangeDays - 1);
            $previousStart = (clone $start)->subDays($rangeDays);
            $previousEnd = (clone $start)->subDay();

            $rows = $this->enrollmentQuery($filters)
                ->whereNotNull('enrollment_date')
                ->whereBetween('enrollment_date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('enrollment_date as date, COUNT(*) as total')
                ->groupBy('enrollment_date')
                ->orderBy('enrollment_date')
                ->get()
                ->keyBy(fn($row) => is_string($row->date) ? $row->date : $row->date->format('Y-m-d'));

            $trend = [];
            $maxTotal = 0;
            $sum = 0;

            foreach (CarbonPeriod::create($start, $end) as $date) {
                $key = $date->format('Y-m-d');
                $row = $rows->get($key);
                $total = (int) ($row->total ?? 0);
                $sum += $total;

                $trend[] = [
                    'label' => $date->format('m/d'),
                    'total' => $total,
                ];

                $maxTotal = max($maxTotal, $total);
            }

            $previousRows = $this->enrollmentQuery($filters)
                ->whereNotNull('enrollment_date')
                ->whereBetween('enrollment_date', [$previousStart->toDateString(), $previousEnd->toDateString()])
                ->selectRaw('enrollment_date as date, COUNT(*) as total')
                ->groupBy('enrollment_date')
                ->orderBy('enrollment_date')
                ->get()
                ->keyBy(fn($row) => is_string($row->date) ? $row->date : $row->date->format('Y-m-d'));

            $previousTrend = [];
            $previousMaxTotal = 0;
            $previousTotal = 0;
            foreach (CarbonPeriod::create($previousStart, $previousEnd) as $date) {
                $key = $date->format('Y-m-d');
                $row = $previousRows->get($key);
                $total = (int) ($row->total ?? 0);
                $previousTotal += $total;

                $previousTrend[] = [
                    'label' => $date->format('m/d'),
                    'total' => $total,
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
                'sum' => $sum,
                'delta' => $delta,
                'direction' => $direction,
                'delta_percent' => $deltaPercent,
                'previous_items' => $previousTrend,
                'previous_max' => $previousMaxTotal,
            ];
        });
    }

    public function enrollmentBreakdown(array $filters): array
    {
        return $this->rememberDashboard($filters, 'enrollment_breakdown', 5, function () use ($filters) {
            $counts = $this->enrollmentQuery($filters)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $colorMap = [
                EnrollmentStatus::Active->value => 'emerald',
                EnrollmentStatus::New->value => 'indigo',
                EnrollmentStatus::Returning->value => 'sky',
                EnrollmentStatus::Completed->value => 'slate',
                EnrollmentStatus::Withdrawn->value => 'amber',
                EnrollmentStatus::Failed->value => 'red',
            ];

            $items = [];
            foreach (EnrollmentStatus::cases() as $status) {
                $items[] = [
                    'status' => $status->value,
                    'label' => $status->label(),
                    'total' => (int) ($counts[$status->value] ?? 0),
                    'color' => $colorMap[$status->value] ?? 'slate',
                ];
            }

            $max = !empty($items) ? max(array_column($items, 'total')) : 0;

            return [
                'items' => $items,
                'max' => $max > 0 ? $max : 1,
            ];
        }, extra: ['focus' => 'enrollment']);
    }

    public function genderBreakdown(array $filters): array
    {
        return $this->rememberDashboard($filters, 'gender_breakdown', 5, function () use ($filters) {
            $query = $this->enrollmentQuery($filters)
                ->join('students', 'students.id', '=', 'student_enrollments.student_id');

            $counts = $query
                ->select('students.gender', DB::raw('COUNT(*) as total'))
                ->groupBy('students.gender')
                ->pluck('total', 'students.gender');

            $male = (int) ($counts[Gender::Male->value] ?? 0);
            $female = (int) ($counts[Gender::Female->value] ?? 0);
            $total = $male + $female;

            $maleRatio = (int) StatsHelper::percentage($male, $total);
            $femaleRatio = $total > 0 ? 100 - $maleRatio : 0;

            return [
                'total' => $total,
                'male_ratio' => $maleRatio,
                'female_ratio' => $femaleRatio,
                'items' => [
                    [
                        'label' => Gender::Male->label(),
                        'total' => $male,
                        'ratio' => $maleRatio,
                        'color' => 'indigo',
                    ],
                    [
                        'label' => Gender::Female->label(),
                        'total' => $female,
                        'ratio' => $femaleRatio,
                        'color' => 'rose',
                    ],
                ],
            ];
        });
    }
}
