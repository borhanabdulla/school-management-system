<?php

namespace App\Domains\Academic\Subject\Actions;

use App\Domains\Academic\Subject\Models\GradeSubject;
use Illuminate\Validation\ValidationException;

class UpdateCurriculumAction
{
    public function execute(GradeSubject $pivotRecord, array $data): void
    {
        // إذا تم تغيير الترم أثناء التعديل، يجب التأكد من عدم حدوث تكرار
        if (isset($data['term_type']) && $data['term_type'] !== $pivotRecord->term_type) {
            $exists = GradeSubject::where('grade_id', $pivotRecord->grade_id)
                ->where('subject_id', $pivotRecord->subject_id)
                ->where('term_type', $data['term_type'])
                ->where('id', '!=', $pivotRecord->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages(['term_type' => 'يوجد بالفعل سجل لهذه المادة في الترم المختار.']);
            }
        }

        $pivotRecord->update($data);
    }
}
