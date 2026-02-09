<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Academic\Student\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GrantFinancialExceptionAction
{
    public function execute(int $studentId, int $academicYearId, string $reason, int $userId): void
    {
        DB::table('financial_clearance_overrides')->updateOrInsert(
            [
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
            ],
            [
                'reason' => $reason,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(), // Add updated_at for updateOrInsert
            ]
        );
    }
}
