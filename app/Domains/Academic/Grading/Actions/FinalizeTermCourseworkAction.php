<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Grading\Services\GradingHealthGate;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use Illuminate\Support\Facades\Log;

/**
 * FinalizeTermCourseworkAction
 * 
 * أداة إدارية لإعادة الحساب الجماعي (Bulk Recompute) لدرجات أعمال السنة.
 * تقوم بتجميع الدرجات الشهرية لكل الطلاب في الترم.
 */
class FinalizeTermCourseworkAction
{
    public function __construct(
        private AggregateGradebookToTemplateMarksAction $aggregateAction
    ) {
    }

    public function execute(
        Term $term,
        ?int $gradeId = null,
        ?int $subjectId = null,
        ?ClassSection $classSection = null
    ): void
    {
        app(AcademicWriteGuard::class)->assertTermNotCompleted($term->id);
        app(GradingHealthGate::class)->assertTermHealthy($term, 'إعادة تجميع أعمال السنة');

        Log::info("Starting FinalizeTermCourseworkAction", [
            'term_id' => $term->id,
            'grade_id' => $gradeId,
            'subject_id' => $subjectId,
            'class_section_id' => $classSection?->id,
        ]);

        // 1. تحديد المواد الدراسية المستهدفة
        $query = CourseOffering::where('term_id', $term->id);

        if ($classSection) {
            $query->where('class_section_id', $classSection->id);
        } elseif ($gradeId) {
            $query->whereHas('classSection', fn($q) => $q->where('grade_id', $gradeId));
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $courseOfferings = $query->with(['classSection', 'subject'])->get();

        foreach ($courseOfferings as $courseOffering) {
            $this->processCourseOffering($courseOffering, $term);
        }

        Log::info("Completed FinalizeTermCourseworkAction", [
            'term_id' => $term->id,
            'grade_id' => $gradeId,
            'subject_id' => $subjectId,
            'class_section_id' => $classSection?->id,
        ]);
    }

    private function processCourseOffering(CourseOffering $courseOffering, Term $term): void
    {
        // 2. تأكد من وجود إعدادات التقييم للمادة
        $config = SubjectGradingConfig::where('subject_id', $courseOffering->subject_id)
            ->where('term_id', $term->id)
            ->where('grade_id', $courseOffering->classSection->grade_id)
            ->first();

        if (!$config || !$config->template) {
            return;
        }

        // 3. جلب طلاب الشعبة عبر التسجيلات (حتى لا نتأثر بالترحيل)
        $enrollments = StudentEnrollment::query()
            ->where('academic_year_id', $term->academic_year_id)
            ->where('class_section_id', $courseOffering->class_section_id)
            ->with('student')
            ->get();

        $students = $enrollments
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->values();

        foreach ($students as $student) {
            try {
                $this->aggregateAction->execute($courseOffering, $student->id, $term->id);
            } catch (\Exception $e) {
                Log::error("Failed to aggregate for student", [
                    'student_id' => $student->id,
                    'course_offering_id' => $courseOffering->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
