<?php

namespace App\Domains\Academic\Grade\Services;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grade\Data\GradeData;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

/**
 * GradeService - خدمة الكتابة للصفوف الدراسية.
 *
 * تحتوي على عمليات الإنشاء والتعديل والحذف مع التحقق من الصحة.
 * لا تحتوي على عمليات قراءة. استخدم GradeLookupService للقراءة.
 *
 * @see GradeLookupService للعمليات القرائية.
 */
class GradeService
{
    public function __construct(
        protected \App\Domains\Academic\Grade\Actions\CreateGradeAction $createAction,
        protected \App\Domains\Academic\Grade\Actions\UpdateGradeAction $updateAction,
        protected \App\Domains\Academic\Grade\Actions\DeleteGradeAction $deleteAction,
    ) {
    }

    public function createGrade(array $data): Grade
    {
        return DB::transaction(function () use ($data) {
            $this->validateGradeRules($data);

            $gradeData = GradeData::fromArray($data);
            $grade = $this->createAction->execute($gradeData);

            GradeLookupService::invalidateCache($grade->educational_stage_id);

            return $grade;
        });
    }

    public function updateGrade(Grade $grade, array $data): Grade
    {
        return DB::transaction(function () use ($grade, $data) {
            $this->validateGradeRules($data, $grade->id);

            if (!empty($data['next_grade_id'])) {
                $this->detectCircularReference($grade->id, $data['next_grade_id']);
            }

            $gradeData = GradeData::fromArray(array_merge($grade->toArray(), $data));
            $updatedGrade = $this->updateAction->execute($grade, $gradeData);

            GradeLookupService::invalidateCache($grade->educational_stage_id);
            // If stage changed, invalidate old stage cache too
            if (isset($data['educational_stage_id']) && $data['educational_stage_id'] != $grade->educational_stage_id) {
                GradeLookupService::invalidateCache($data['educational_stage_id']);
            }

            return $updatedGrade;
        });
    }

    public function deleteGrade(Grade $grade): void
    {
        if ($grade->sections()->exists()) {
            throw new Exception("لا يمكن حذف الصف الدراسي لأنه يحتوي على شعب دراسية.");
        }

        if ($grade->subjects()->exists()) {
            throw new Exception("لا يمكن حذف الصف الدراسي لأنه مرتبط بمواد دراسية.");
        }

        // فك الارتباط الآمن قبل الحذف
        Grade::where('next_grade_id', $grade->id)->update(['next_grade_id' => null]);

        $this->deleteAction->execute($grade);
        GradeLookupService::invalidateCache($grade->educational_stage_id);
    }

    // --- HELPER LOGIC ---

    protected function validateGradeRules(array $data, $ignoreId = null): void
    {
        if (isset($data['educational_stage_id']) && isset($data['level_order'])) {
            $exists = Grade::where('educational_stage_id', $data['educational_stage_id'])
                ->where('level_order', $data['level_order'])
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages(['level_order' => 'يوجد صف آخر بنفس رقم الترتيب في هذه المرحلة.']);
            }
        }

        if (!empty($data['next_grade_id']) && $data['next_grade_id'] == $ignoreId) {
            throw ValidationException::withMessages(['next_grade_id' => 'لا يمكن للصف أن يكون تالياً لنفسه.']);
        }
    }

    protected function detectCircularReference($sourceId, $targetId): void
    {
        $currentId = $targetId;
        $visited = [];

        for ($i = 0; $i < 20; $i++) {
            if (!$currentId)
                break;

            if ($currentId == $sourceId) {
                throw new \App\Exceptions\Academic\CircularReferenceException('خطأ: هذا الربط ينشئ حلقة مفرغة (دائرة مغلقة) في نظام الترفيع.');
            }

            if (in_array($currentId, $visited))
                break;
            $visited[] = $currentId;

            $nextGrade = Grade::find($currentId);
            $currentId = $nextGrade ? $nextGrade->next_grade_id : null;
        }
    }
}
