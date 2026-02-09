<?php

namespace App\Domains\Shared\Services\Dashboard;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardQueries;
use App\Domains\Shared\Services\StatsHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardDrawerService
{
    use HasDashboardQueries;

    public function __construct(
        protected MainDashboardDataService $dataService
    ) {
    }

    public function buildDrawerData(string $type, ?string $payload, array $filters, ?AcademicYear $selectedYear): array
    {
        return match ($type) {
            'health' => $this->healthData($selectedYear, $filters, $payload),
            'attendance-day' => $this->attendanceDayData($filters, $payload),
            'finance' => $this->financeData($filters, $payload),
            'enrollment-status' => $this->enrollmentStatusData($filters, $payload),
            default => [],
        };
    }

    public function healthData(?AcademicYear $selectedYear, array $filters, ?string $payload = null): array
    {
        $summary = $this->dataService->readinessSummary($selectedYear, $filters);
        $items = collect($summary['items'] ?? [])
            ->filter(fn($item) => $item['has_issues'] ?? false)
            ->values();

        $blocking = $items->filter(fn($item) => $item['is_blocking'] ?? false)->values();
        $warnings = $items->filter(fn($item) => $item['is_warning'] ?? false)->values();

        return [
            'year' => $summary['year_name'] ?? null,
            'can_close' => $summary['can_close'] ?? false,
            'blocking' => $blocking->toArray(),
            'warnings' => $warnings->toArray(),
            'selected' => $payload,
        ];
    }

    public function attendanceDayData(array $filters, ?string $payload): array
    {
        $date = $payload ? Carbon::parse($payload)->toDateString() : now()->toDateString();

        return $this->rememberDrawer($filters, 'attendance_day', 2, function () use ($filters, $date) {
            $counts = $this->attendanceQuery($filters)
                ->whereDate('date', $date)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $items = [];
            foreach ([AttendanceStatus::PRESENT, AttendanceStatus::LATE, AttendanceStatus::ABSENT, AttendanceStatus::EXCUSED] as $status) {
                $items[] = [
                    'label' => $status->label(),
                    'total' => (int) ($counts[$status->value] ?? 0),
                    'color' => $status->color(),
                ];
            }

            $presentValue = AttendanceStatus::PRESENT->value;
            $classes = $this->attendanceQuery($filters)
                ->whereDate('date', $date)
                ->join('class_sections', 'class_sections.id', '=', 'attendances.class_section_id')
                ->join('grades', 'grades.id', '=', 'class_sections.grade_id')
                ->selectRaw(
                    "class_sections.id as section_id, class_sections.name as section_name, grades.name as grade_name, " .
                    "SUM(CASE WHEN attendances.status = '{$presentValue}' THEN 1 ELSE 0 END) as present, COUNT(*) as total"
                )
                ->groupBy('class_sections.id', 'class_sections.name', 'grades.name')
                ->orderByRaw(
                    "CASE WHEN COUNT(*) > 0 THEN SUM(CASE WHEN attendances.status = '{$presentValue}' THEN 1 ELSE 0 END) * 1.0 / COUNT(*) ELSE 0 END"
                )
                ->limit(5)
                ->get()
                ->map(function ($row) {
                    $rate = (int) StatsHelper::percentage((int) $row->present, (int) $row->total);
                    return [
                        'label' => trim(($row->grade_name ?? '') . ' - ' . ($row->section_name ?? 'غير محدد')),
                        'present' => (int) $row->present,
                        'total' => (int) $row->total,
                        'rate' => $rate,
                    ];
                })
                ->toArray();

            return [
                'date' => $date,
                'items' => $items,
                'classes' => $classes,
            ];
        }, ['date' => $date]);
    }

    public function financeData(array $filters, ?string $payload): array
    {
        $bucket = $payload;
        $selectedStatus = null;

        if (is_string($payload) && str_starts_with($payload, 'invoice-status:')) {
            $selectedStatus = substr($payload, strlen('invoice-status:')) ?: null;
            $bucket = null;
        }

        $statusEnum = $selectedStatus ? InvoiceStatus::tryFrom($selectedStatus) : null;
        $summary = $statusEnum
            ? $this->dataService->invoiceStatusSummary($statusEnum, $filters)
            : $this->dataService->financeSummary($filters);

        return $this->rememberDrawer($filters, 'finance_drawer', 3, function () use ($filters, $bucket, $statusEnum, $summary) {
            $daysExpr = $this->daysOverdueExpression();
            $aging = $this->invoiceQuery($filters)
                ->outstanding()
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->selectRaw(
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as b0_30, " .
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as b31_60, " .
                    "SUM(CASE WHEN {$daysExpr} BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as b61_90, " .
                    "SUM(CASE WHEN {$daysExpr} > 90 THEN 1 ELSE 0 END) as b90_plus"
                )
                ->first();

            $topUnpaidQuery = $this->invoiceQuery($filters)
                ->outstanding()
                ->join('students', 'students.id', '=', 'invoices.student_id')
                ->leftJoin('class_sections', 'class_sections.id', '=', 'students.current_class_section_id')
                ->leftJoin('grades', 'grades.id', '=', 'class_sections.grade_id');

            if ($statusEnum) {
                $topUnpaidQuery->where('invoices.status', $statusEnum->value);
            }

            if ($bucket) {
                $topUnpaidQuery->whereNotNull('due_date')->whereDate('due_date', '<', today());
                $topUnpaidQuery->when($bucket === 'aging:0-30', fn($q) => $q->whereRaw("{$daysExpr} BETWEEN 1 AND 30"));
                $topUnpaidQuery->when($bucket === 'aging:31-60', fn($q) => $q->whereRaw("{$daysExpr} BETWEEN 31 AND 60"));
                $topUnpaidQuery->when($bucket === 'aging:61-90', fn($q) => $q->whereRaw("{$daysExpr} BETWEEN 61 AND 90"));
                $topUnpaidQuery->when($bucket === 'aging:90+', fn($q) => $q->whereRaw("{$daysExpr} > 90"));
            }

            $topUnpaid = $topUnpaidQuery
                ->selectRaw(
                    'class_sections.id as section_id, class_sections.name as section_name, grades.name as grade_name, ' .
                    'SUM(invoices.total_amount - invoices.paid_amount) as outstanding, COUNT(*) as total'
                )
                ->groupBy('class_sections.id', 'class_sections.name', 'grades.name')
                ->orderByDesc('outstanding')
                ->limit(5)
                ->get()
                ->map(function ($row) {
                    $label = trim(($row->grade_name ?? '') . ' - ' . ($row->section_name ?? 'غير محدد'));
                    return [
                        'label' => $label !== '-' ? $label : 'غير محدد',
                        'outstanding' => (float) $row->outstanding,
                        'total' => (int) $row->total,
                    ];
                })
                ->toArray();

            return [
                'summary' => $summary,
                'bucket' => $bucket,
                'selected_status' => $statusEnum?->label(),
                'aging' => [
                    '0_30' => (int) ($aging->b0_30 ?? 0),
                    '31_60' => (int) ($aging->b31_60 ?? 0),
                    '61_90' => (int) ($aging->b61_90 ?? 0),
                    '90_plus' => (int) ($aging->b90_plus ?? 0),
                ],
                'top_unpaid' => $topUnpaid,
            ];
        }, ['bucket' => $bucket, 'status' => $statusEnum?->value]);
    }

    public function enrollmentStatusData(array $filters, ?string $payload): array
    {
        $status = null;
        if (is_string($payload) && str_starts_with($payload, 'enrollment-status:')) {
            $status = substr($payload, strlen('enrollment-status:')) ?: null;
        }

        return $this->rememberDrawer($filters, 'enrollment_status', 3, function () use ($filters, $status) {
            $statusEnum = $status ? EnrollmentStatus::tryFrom($status) : null;
            $query = $this->enrollmentQuery($filters);
            if ($statusEnum) {
                $query->where('status', $statusEnum->value);
            }

            $total = $query->count();

            $topGrades = $this->enrollmentQuery($filters)
                ->when($statusEnum, fn($q) => $q->where('status', $statusEnum->value))
                ->join('grades', 'grades.id', '=', 'student_enrollments.grade_id')
                ->select('grades.name', DB::raw('COUNT(*) as total'))
                ->groupBy('grades.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn($row) => [
                    'label' => $row->name ?? 'غير محدد',
                    'total' => (int) $row->total,
                ])
                ->toArray();

            return [
                'status' => $statusEnum?->label() ?? 'جميع الحالات',
                'total' => $total,
                'top_grades' => $topGrades,
            ];
        }, ['status' => $status]);
    }

    protected function rememberDrawer(array $filters, string $suffix, int $minutes, callable $callback, array $extra = [])
    {
        $key = $this->drawerCacheKey($filters, $suffix, $extra);

        return Cache::remember($key, now()->addMinutes($minutes), $callback);
    }

    protected function drawerCacheKey(array $filters, string $suffix, array $extra): string
    {
        $parts = ['dashboard_drawer', $suffix];
        $parts[] = 'year:' . ($filters['academicYearId'] ?? 'all');
        $parts[] = 'term:' . ($filters['termId'] ?? 'all');
        $parts[] = 'grade:' . ($filters['gradeId'] ?? 'all');
        $parts[] = 'range:' . ($filters['range'] ?? 'all');

        foreach ($extra as $key => $value) {
            $parts[] = $key . ':' . ($value ?? 'all');
        }

        return implode('|', $parts);
    }

}
