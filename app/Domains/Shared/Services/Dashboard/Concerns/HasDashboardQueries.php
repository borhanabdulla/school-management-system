<?php

namespace App\Domains\Shared\Services\Dashboard\Concerns;

use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Support\Facades\DB;

trait HasDashboardQueries
{
    protected function enrollmentQuery(array $filters)
    {
        $query = StudentEnrollment::query();

        if (!empty($filters['academicYearId'])) {
            $query->where('academic_year_id', $filters['academicYearId']);
        }

        if (!empty($filters['gradeId'])) {
            $query->where('grade_id', $filters['gradeId']);
        }

        return $query;
    }

    protected function attendanceQuery(array $filters)
    {
        $query = Attendance::query();

        if (!empty($filters['academicYearId'])) {
            $query->where('attendances.academic_year_id', $filters['academicYearId']);
        }

        if (!empty($filters['termId'])) {
            $query->where('attendances.term_id', $filters['termId']);
        }

        if (!empty($filters['gradeId'])) {
            $gradeId = $filters['gradeId'];
            $query->whereHas('classSection', function ($sectionQuery) use ($gradeId) {
                $sectionQuery->where('grade_id', $gradeId);
            });
        }

        return $query;
    }

    protected function invoiceQuery(array $filters)
    {
        $query = Invoice::query();

        if (!empty($filters['academicYearId'])) {
            $query->where('academic_year_id', $filters['academicYearId']);
        }

        return $query;
    }

    protected function daysOverdueExpression(): string
    {
        return "CAST(julianday('now') - julianday(due_date) as integer)";
    }
}
