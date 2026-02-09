<?php

namespace App\Domains\Academic\Subject\Actions;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Data\SubjectAssignmentData;
use Illuminate\Validation\ValidationException;

class AssignSubjectToGradeAction
{
    public function execute(Grade $grade, SubjectAssignmentData $data): void
    {
        // التحقق: هل هذه المادة موجودة في *نفس الترم* لهذا الصف؟
        $exists = $grade->subjects()
            ->where('subject_id', $data->subject_id)
            ->wherePivot('term_type', $data->term_type)
            ->exists();

        if ($exists) {
            $termName = match ($data->term_type) {
                'full_year' => 'كامل السنة',
                'term_1' => 'الترم الأول',
                'term_2' => 'الترم الثاني',
                'term_3' => 'الترم الثالث',
                default => $data->term_type,
            };

            throw ValidationException::withMessages([
                'subject_id' => __('validation.custom.subject_already_assigned', ['term' => $termName])
            ]);
        }

        $grade->subjects()->attach($data->subject_id, [
            'credit_hours' => $data->credit_hours,
            'term_type' => $data->term_type,
            'is_active' => $data->is_active
        ]);
    }
}
