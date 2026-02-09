<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Services\AcademicWriteGuard;

final class RecordMonthlyGradeAction
{
    public function execute(
        CourseOffering $offering,
        int $studentId,
        int $monthId,
        string $categoryKey,
        ?float $score,
        float $maxScore,
        string $categoryLabel,
        ?int $gradedByUserId
    ): MonthlyGrade {
        $month = GradebookMonth::find($monthId);
        $termId = $month?->term_id ?? $offering->term_id;
        if ($termId) {
            app(AcademicWriteGuard::class)->assertTermNotCompleted($termId);
        }

        $categoryKey = trim($categoryKey);
        if ($categoryKey === '') {
            $categoryKey = GradebookSettings::generateCategoryKey($categoryLabel);
        }

        $grade = MonthlyGrade::query()
            ->where([
                'student_id' => $studentId,
                'course_offering_id' => $offering->id,
                'gradebook_month_id' => $monthId,
            ])
            ->where(function ($query) use ($categoryKey, $categoryLabel) {
                $query->where('category_key', $categoryKey)
                    ->orWhere('category', $categoryLabel);
            })
            ->first();

        if (! $grade) {
            $grade = new MonthlyGrade([
                'student_id' => $studentId,
                'course_offering_id' => $offering->id,
                'gradebook_month_id' => $monthId,
            ]);
        }

        $grade->fill([
            'category_key' => $categoryKey,
            'category' => $categoryLabel,
            'score' => $score,
            'max_score' => $maxScore,
            'graded_by' => $gradedByUserId,
        ]);

        $grade->save();

        return $grade;
    }
}
