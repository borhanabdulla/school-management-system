<?php

namespace App\Domains\Shared\Services\Dashboard;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\HR\Staff\Enums\StaffStatus;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Leave\Enums\LeaveRequestStatus;
use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardQueries;
use App\Domains\Shared\Services\Dashboard\DTOs\Alerts\DashboardAlert;
use App\Domains\Shared\Services\Dashboard\DTOs\Insights\DashboardInsight;
use App\Domains\Shared\Services\Dashboard\DTOs\Kpis\DashboardStats;
use App\Domains\Shared\Services\Dashboard\DTOs\Kpis\HealthScore;
use App\Domains\Shared\Services\Dashboard\Sections\AttendanceDashboardService;
use App\Domains\Shared\Services\Dashboard\Sections\EnrollmentDashboardService;
use App\Domains\Shared\Services\Dashboard\Sections\EventsDashboardService;
use App\Domains\Shared\Services\Dashboard\Sections\FinanceDashboardService;
use App\Domains\Shared\Services\Dashboard\Sections\PeopleDashboardService;
use App\Domains\Shared\Services\Dashboard\Sections\ReadinessDashboardService;
use App\Domains\Shared\Services\Dashboard\Sections\SystemSnapshotDashboardService;
use App\Domains\Shared\Services\StatsHelper;

class MainDashboardDataService
{
    use HasDashboardQueries;

    public function __construct(
        protected AttendanceDashboardService $attendanceService,
        protected EnrollmentDashboardService $enrollmentService,
        protected FinanceDashboardService $financeService,
        protected ReadinessDashboardService $readinessService,
        protected PeopleDashboardService $peopleService,
        protected EventsDashboardService $eventsService,
        protected SystemSnapshotDashboardService $snapshotService
    ) {
    }

    public function gradeDistribution(array $filters): array
    {
        return $this->enrollmentService->gradeDistribution($filters);
    }

    public function stats(array $filters, array $readinessSummary, array $finance): array
    {
        $totalStudents = !empty($filters['academicYearId'])
            ? $this->enrollmentQuery($filters)->distinct('student_id')->count('student_id')
            : Student::query()->count();

        $activeStudents = $this->enrollmentQuery($filters)
            ->where('status', EnrollmentStatus::Active->value)
            ->count();

        $newEnrollments = $this->enrollmentQuery($filters)
            ->where('status', EnrollmentStatus::New->value)
            ->count();

        $teachers = Teacher::query()->count();
        $activeStaff = Staff::query()->where('status', StaffStatus::Active->value)->count();
        $leavePending = LeaveRequest::query()->where('status', LeaveRequestStatus::Pending->value)->count();

        $attendanceTodayQuery = $this->attendanceQuery($filters)->whereDate('date', today());
        $attendanceToday = (clone $attendanceTodayQuery)
            ->where('status', AttendanceStatus::PRESENT->value)
            ->count();
        $attendanceTotalToday = (clone $attendanceTodayQuery)->count();
        $attendanceRate = (int) StatsHelper::percentage($attendanceToday, $attendanceTotalToday);

        return (new DashboardStats(
            totalStudents: $totalStudents,
            activeStudents: $activeStudents,
            newEnrollments: $newEnrollments,
            teachers: $teachers,
            activeStaff: $activeStaff,
            attendanceToday: $attendanceToday,
            attendanceTotalToday: $attendanceTotalToday,
            attendanceRateToday: $attendanceRate,
            leavePending: $leavePending,
            readinessBlocking: (int) ($readinessSummary['blocking_count'] ?? 0),
            readinessWarnings: (int) ($readinessSummary['warning_count'] ?? 0),
            financePaidRatio: (int) ($finance['paid_ratio'] ?? 0)
        ))->toArray();
    }

    public function healthScore(array $stats, array $finance): array
    {
        $score = 100;

        $score -= ($stats['readiness_blocking'] ?? 0) * 12;
        $score -= ($stats['readiness_warnings'] ?? 0) * 4;

        if (($stats['attendance_rate_today'] ?? 0) < 85) {
            $score -= (85 - $stats['attendance_rate_today']) * 0.5;
        }

        if (($finance['paid_ratio'] ?? 0) < 85) {
            $score -= (85 - $finance['paid_ratio']) * 0.4;
        }

        $score = (int) max(0, min(100, round($score)));

        if ($score >= 85) {
            $label = 'ممتاز';
            $color = 'emerald';
        } elseif ($score >= 70) {
            $label = 'جيد';
            $color = 'indigo';
        } elseif ($score >= 55) {
            $label = 'متوسط';
            $color = 'amber';
        } else {
            $label = 'يحتاج دعم';
            $color = 'red';
        }

        return (new HealthScore(
            score: $score,
            label: $label,
            color: $color
        ))->toArray();
    }

    public function termsSummary(?int $academicYearId): array
    {
        $query = Term::query();
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return [
            'active' => (clone $query)->where('status', \App\Domains\Academic\Term\Enums\TermStatus::Active)->count(),
            'completed' => (clone $query)->where('status', \App\Domains\Academic\Term\Enums\TermStatus::Completed)->count(),
            'total' => (clone $query)->count(),
        ];
    }

    public function alerts(array $readinessSummary, array $finance, string $severity = 'all'): array
    {
        $alerts = [];

        if (!empty($readinessSummary['items'])) {
            foreach ($readinessSummary['items'] as $item) {
                if (!($item['has_issues'] ?? false)) {
                    continue;
                }

                $alerts[] = new DashboardAlert(
                    type: ($item['is_blocking'] ?? false) ? 'blocking' : 'warning',
                    title: $item['label'] ?? 'تنبيه',
                    message: $item['message'] ?? '',
                    route: $item['route'] ?? null,
                    routeParams: $item['route_params'] ?? [],
                    drawer: 'health'
                );
            }
        }

        $leavePending = LeaveRequest::query()->where('status', LeaveRequestStatus::Pending->value)->count();
        if ($leavePending > 0) {
            $alerts[] = new DashboardAlert(
                type: 'warning',
                title: 'طلبات إجازة معلقة',
                message: "لديك {$leavePending} طلب إجازة بحاجة للمراجعة.",
                route: 'hr.leave.approvals'
            );
        }

        if (($finance['outstanding'] ?? 0) > 0) {
            $alerts[] = new DashboardAlert(
                type: 'warning',
                title: 'مستحقات مالية معلقة',
                message: 'يوجد مستحقات غير محصّلة تحتاج متابعة.',
                route: 'finance.invoices.index',
                drawer: 'finance'
            );
        }

        if ($severity !== 'all') {
            $alerts = array_filter($alerts, fn(DashboardAlert $alert) => $alert->type === $severity);
        }

        return array_slice(
            array_map(fn(DashboardAlert $alert) => $alert->toArray(), array_values($alerts)),
            0,
            6
        );
    }

    public function insights(array $stats, array $finance): array
    {
        $insights = [];

        if (($stats['attendance_rate_today'] ?? 0) < 85) {
            $insights[] = new DashboardInsight(
                type: 'warning',
                title: 'مستوى حضور منخفض',
                message: "نسبة الحضور اليوم {$stats['attendance_rate_today']}%، يُنصح بمراجعة الصفوف ذات الغياب المرتفع."
            );
        } else {
            $insights[] = new DashboardInsight(
                type: 'success',
                title: 'حضور مستقر',
                message: "الحضور اليوم في مستوى جيد ({$stats['attendance_rate_today']}%)."
            );
        }

        if (($finance['outstanding'] ?? 0) > 0) {
            $insights[] = new DashboardInsight(
                type: 'warning',
                title: 'التحصيل بحاجة متابعة',
                message: 'لا تزال هناك مستحقات مالية مفتوحة تستدعي خطة تحصيل.'
            );
        } else {
            $insights[] = new DashboardInsight(
                type: 'success',
                title: 'التحصيل مكتمل',
                message: 'لا توجد مستحقات مالية معلقة حالياً.'
            );
        }

        if (($stats['readiness_blocking'] ?? 0) > 0) {
            $insights[] = new DashboardInsight(
                type: 'blocking',
                title: 'جاهزية الإغلاق بحاجة تدخل',
                message: 'هناك عناصر مانعة يجب حلّها قبل إغلاق السنة الدراسية.'
            );
        } else {
            $insights[] = new DashboardInsight(
                type: 'success',
                title: 'الجاهزية جيدة',
                message: 'لا توجد مشاكل مانعة في مؤشرات إغلاق السنة حتى الآن.'
            );
        }

        return array_map(fn(DashboardInsight $insight) => $insight->toArray(), $insights);
    }

    public function attendanceTrend(array $filters): array
    {
        return $this->attendanceService->attendanceTrend($filters);
    }

    public function attendanceHeatmap(array $filters): array
    {
        return $this->attendanceService->attendanceHeatmap($filters);
    }

    public function enrollmentTrend(array $filters): array
    {
        return $this->enrollmentService->enrollmentTrend($filters);
    }

    public function financeTrend(array $filters): array
    {
        return $this->financeService->financeTrend($filters);
    }

    public function attendanceBreakdown(array $filters): array
    {
        return $this->attendanceService->attendanceBreakdown($filters);
    }

    public function enrollmentBreakdown(array $filters): array
    {
        return $this->enrollmentService->enrollmentBreakdown($filters);
    }

    public function invoiceBreakdown(array $filters): array
    {
        return $this->financeService->invoiceBreakdown($filters);
    }

    public function staffBreakdown(): array
    {
        return $this->peopleService->staffBreakdown();
    }

    public function topTeachers(): array
    {
        return $this->peopleService->topTeachers();
    }

    public function genderBreakdown(array $filters): array
    {
        return $this->enrollmentService->genderBreakdown($filters);
    }

    public function upcomingEvents(array $filters): array
    {
        return $this->eventsService->upcomingEvents($filters);
    }

    public function systemSnapshot(): array
    {
        return $this->snapshotService->systemSnapshot();
    }

    public function financeSummary(array $filters): array
    {
        return $this->financeService->financeSummary($filters);
    }

    public function financeAgingBuckets(array $filters): array
    {
        return $this->financeService->financeAgingBuckets($filters);
    }

    public function readinessSummary(?AcademicYear $selectedYear, array $filters): array
    {
        return $this->readinessService->readinessSummary($selectedYear, $filters);
    }

    public function readinessPareto(array $summary, array $filters): array
    {
        return $this->readinessService->readinessPareto($summary, $filters);
    }

    public function invoiceStatusSummary(InvoiceStatus $status, array $filters): array
    {
        return $this->financeService->invoiceStatusSummary($status, $filters);
    }
}
