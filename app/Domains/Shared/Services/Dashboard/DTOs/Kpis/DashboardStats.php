<?php

namespace App\Domains\Shared\Services\Dashboard\DTOs\Kpis;

final readonly class DashboardStats
{
    public function __construct(
        public int $totalStudents,
        public int $activeStudents,
        public int $newEnrollments,
        public int $teachers,
        public int $activeStaff,
        public int $attendanceToday,
        public int $attendanceTotalToday,
        public int $attendanceRateToday,
        public int $leavePending,
        public int $readinessBlocking,
        public int $readinessWarnings,
        public int $financePaidRatio
    ) {
    }

    public function toArray(): array
    {
        return [
            'total_students' => $this->totalStudents,
            'active_students' => $this->activeStudents,
            'new_enrollments' => $this->newEnrollments,
            'teachers' => $this->teachers,
            'active_staff' => $this->activeStaff,
            'attendance_today' => $this->attendanceToday,
            'attendance_total_today' => $this->attendanceTotalToday,
            'attendance_rate_today' => $this->attendanceRateToday,
            'leave_pending' => $this->leavePending,
            'readiness_blocking' => $this->readinessBlocking,
            'readiness_warnings' => $this->readinessWarnings,
            'finance_paid_ratio' => $this->financePaidRatio,
        ];
    }
}
