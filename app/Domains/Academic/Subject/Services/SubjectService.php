<?php

namespace App\Domains\Academic\Subject\Services;

use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\GradeSubject;
use App\Domains\Academic\Subject\Actions\CreateSubjectAction;
use App\Domains\Academic\Subject\Actions\AssignSubjectToGradeAction;
use App\Domains\Academic\Subject\Actions\UpdateCurriculumAction;
use App\Domains\Academic\Subject\Data\SubjectData;
use App\Domains\Academic\Subject\Exceptions\SubjectHasGradesException;
use Illuminate\Support\Facades\DB;

class SubjectService
{
    public function __construct(
        protected CreateSubjectAction $createSubjectAction,
        protected AssignSubjectToGradeAction $assignAction,
        protected UpdateCurriculumAction $updateCurriculumAction,
        protected SubjectLookupService $lookupService
    ) {
    }

    // --- عمليات بنك المواد (Library) ---

    /**
     * إنشاء مادة جديدة
     * عملية معقدة تستخدم Action و DTO
     */
    public function createSubject(SubjectData $data): Subject
    {
        $subject = $this->createSubjectAction->execute($data);
        $this->lookupService->invalidateSubjectsListCache();
        return $subject;
    }

    /**
     * تحديث بيانات المادة
     * عملية بسيطة تم دمجها هنا
     */
    public function updateSubject(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        $this->lookupService->invalidateSubjectsListCache();
        return $subject;
    }

    /**
     * حذف مادة
     * عملية بسيطة مع تحقق من قواعد العمل
     * @throws SubjectHasGradesException
     */
    public function deleteSubject(Subject $subject): void
    {
        if ($subject->grades()->exists()) {
            throw SubjectHasGradesException::create($subject->name);
        }

        $subject->delete();
        $this->lookupService->invalidateSubjectsListCache();
    }

    // --- عمليات المنهج (Curriculum) ---

    /**
     * ربط مادة بصف دراسي
     * عملية معقدة تستخدم Action و DTO
     */
    public function assignSubjectToGrade(Grade $grade, array $data): void
    {
        $dto = \App\Domains\Academic\Subject\Data\SubjectAssignmentData::fromArray($data);
        $this->assignAction->execute($grade, $dto);
        $this->lookupService->invalidateCurriculumCache($grade->id);
    }

    /**
     * تحديث بيانات منهج (ساعات، درجات)
     * عملية معقدة تستخدم Action
     */
    public function updateGradeSubject($pivotId, array $data): void
    {
        $pivotRecord = GradeSubject::findOrFail($pivotId);
        $this->updateCurriculumAction->execute($pivotRecord, $data);
        $this->lookupService->invalidateCurriculumCache($pivotRecord->grade_id);
    }

    /**
     * إزالة مادة من منهج صف
     * عملية بسيطة تم دمجها هنا
     */
    public function removeSubjectFromGrade($pivotId): void
    {
        $pivotRecord = GradeSubject::findOrFail($pivotId);
        $gradeId = $pivotRecord->grade_id;

        $pivotRecord->delete();

        $this->lookupService->invalidateCurriculumCache($gradeId);
    }
}