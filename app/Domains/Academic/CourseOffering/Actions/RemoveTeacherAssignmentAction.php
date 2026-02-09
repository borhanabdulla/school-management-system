<?php

namespace App\Domains\Academic\CourseOffering\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;

class RemoveTeacherAssignmentAction
{
    public function execute(int $classSectionId, int $subjectId, int $academicYearId): void
    {
        CourseOffering::where('class_section_id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->delete();
    }
}