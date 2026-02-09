<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Validators;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Exceptions\GradeAlreadyAssignedException;
use Illuminate\Support\Facades\DB;

/**
 * TimetableGradeValidator - التحقق من تعيينات الصفوف
 * 
 * هذه الطبقة مسؤولة عن التحقق من البيانات قبل تنفيذ العمليات.
 * تم فصلها عن Actions لتحقيق مبدأ المسؤولية الواحدة.
 */
class TimetableGradeValidator
{
    /**
     * التحقق من عدم تعيين الصفوف لقوالب أخرى
     * 
     * @throws GradeAlreadyAssignedException
     */
    public function validateGradeAssignments(
        array $gradeIds,
        int $academicYearId,
        ?int $excludeTemplateId = null
    ): void {
        foreach ($gradeIds as $gradeId) {
            $query = DB::table('grade_timetable_template')
                ->where('grade_id', $gradeId)
                ->where('academic_year_id', $academicYearId);

            if ($excludeTemplateId) {
                $query->where('template_id', '!=', $excludeTemplateId);
            }

            $existingAssignment = $query->first();

            if ($existingAssignment) {
                $existingTemplate = TimetableTemplate::find($existingAssignment->template_id);
                throw new GradeAlreadyAssignedException(
                    $gradeId,
                    $existingAssignment->template_id,
                    $existingTemplate
                );
            }
        }
    }
}
