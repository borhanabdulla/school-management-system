<?php

namespace App\Domains\Academic\CourseOffering\Services;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\CourseOffering\Actions\AssignTeacherAction;
use App\Domains\Academic\CourseOffering\Actions\RemoveTeacherAssignmentAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseOfferingService
{
    public function __construct(
        protected AssignTeacherAction $assignAction,
        protected RemoveTeacherAssignmentAction $removeAction
    ) {
    }

    /**
     * تجلب المواد المقررة على الشعبة وتدمجها مع المعلمين المعينين حالياً.
     * تم تحسينها لتقليل استهلاك الذاكرة.
     * 
     * @deprecated استخدم getAssignmentMatrixFromSection() مع شعبة محملة مسبقاً
     */
    public function getAssignmentMatrix(int $classSectionId, int $academicYearId, ?int $termId = null): Collection
    {
        // 1. Eager Loading: جلب الشعبة مع الصف والمواد (أعمدة محددة فقط)
        $section = ClassSection::with([
            'grade.subjects' => function ($query) {
                $query->select('subjects.id', 'subjects.name', 'subjects.code')
                    ->orderBy('subjects.name');
            }
        ])->findOrFail($classSectionId);

        return $this->getAssignmentMatrixFromSection($section, $academicYearId, $termId);
    }

    /**
     * بناء مصفوفة التعيينات من شعبة محملة مسبقاً (تحسين الأداء).
     * يُستخدم عندما تكون الشعبة محملة بالفعل لتجنب الاستعلامات المكررة.
     */
    public function getAssignmentMatrixFromSection(ClassSection $section, int $academicYearId, ?int $termId = null): Collection
    {
        if ($section->grade->subjects->isEmpty()) {
            return collect([]);
        }

        // Bulk Fetch: جلب كل التعيينات لهذه الشعبة في استعلام واحد
        $currentAssignments = CourseOffering::where('class_section_id', $section->id)
            ->where('academic_year_id', $academicYearId)
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->pluck('teacher_id', 'subject_id');

        // Mapping: دمج البيانات في الذاكرة
        return $section->grade->subjects->map(function ($subject) use ($currentAssignments) {
            return [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'assigned_teacher_id' => $currentAssignments[$subject->id] ?? null,
            ];
        });
    }

    /**
     * تعيين معلم لمادة في شعبة معينة.
     */
    public function assignTeacherToSubject(int $classSectionId, int $subjectId, int $teacherId, int $academicYearId, ?int $termId = null): void
    {
        $this->assignAction->execute($classSectionId, $subjectId, $teacherId, $academicYearId, $termId);
    }

    /**
     * إزالة تعيين معلم من مادة.
     */
    public function removeTeacherFromSubject(int $classSectionId, int $subjectId, int $academicYearId): void
    {
        $this->removeAction->execute($classSectionId, $subjectId, $academicYearId);
    }
}
