<?php

namespace App\Domains\Academic\Student\Services;

use App\Domains\Academic\Student\Models\StudentEnrollment;

class StudentPlacementSyncService
{
    /**
     * Synchronize student's current grade/section with the provided enrollment if
     * it belongs to the active academic year.
     */
    public function sync(StudentEnrollment $enrollment): bool
    {
        $student = $enrollment->student;
        if (!$student) {
            return false;
        }

        $currentYear = school()->activeYear();
        if (!$currentYear || $currentYear->id !== $enrollment->academic_year_id) {
            return false;
        }

        $student->updateQuietly([
            'current_grade_id' => $enrollment->grade_id,
            'current_class_section_id' => $enrollment->class_section_id,
        ]);

        return true;
    }
}
