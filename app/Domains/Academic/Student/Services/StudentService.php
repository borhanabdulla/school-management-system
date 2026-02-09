<?php

namespace App\Domains\Academic\Student\Services;

use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Actions\PromoteStudentAction;
use App\Domains\Academic\Student\Exceptions\NoActiveAcademicYearException;
use Illuminate\Support\Facades\DB;

class StudentService
{
    /**
     * Promote students from one grade to another for a new academic year.
     * 
     * @param int $fromGradeId
     * @param int $toGradeId
     * @param int $newAcademicYearId
     * @return int Number of students promoted
     */
    public function promoteStudents($fromGradeId, $toGradeId, $newAcademicYearId)
    {
        // استخدام Action الجديد للترحيل
        $action = app(PromoteStudentAction::class);
        $nextYear = AcademicYear::findOrFail($newAcademicYearId);
        $nextGrade = Grade::findOrFail($toGradeId);

        return DB::transaction(function () use ($fromGradeId, $nextYear, $nextGrade, $action) {
            $activeYear = school()->activeYear();
            if (!$activeYear) {
                throw new NoActiveAcademicYearException();
            }
            $currentYear = AcademicYear::query()
                ->whereKey($activeYear->id)
                ->lockForUpdate()
                ->first();

            // Get all students in the source grade who have passed
            $eligibleEnrollments = StudentEnrollment::where('academic_year_id', $currentYear->id)
                ->where('grade_id', $fromGradeId)
                ->where('status', EnrollmentStatus::Completed)
                ->with('student') // Eager load student
                ->get();

            $count = 0;
            foreach ($eligibleEnrollments as $oldEnrollment) {
                try {
                    $action->execute($oldEnrollment->student, $nextYear, $nextGrade);
                    $count++;
                } catch (\Exception $e) {
                    // Log error or continue? For bulk operations, maybe log and continue
                    // For now, we'll just continue
                    continue;
                }
            }

            return $count;
        });
    }

}
