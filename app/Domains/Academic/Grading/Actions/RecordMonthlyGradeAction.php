<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\Log;

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

        $templateCategoryId = $this->resolveTemplateCategoryId($offering, $month, $categoryKey);
        if (! $templateCategoryId) {
            throw InvalidOperationException::make('لا يمكن تسجيل الدرجة قبل ضبط مابينغ الدفتر الشهري للمادة.');
        }

        $gradeQuery = MonthlyGrade::query()
            ->where([
                'student_id' => $studentId,
                'course_offering_id' => $offering->id,
                'gradebook_month_id' => $monthId,
            ])
            ->where(function ($query) use ($templateCategoryId, $categoryKey, $categoryLabel) {
                if ($templateCategoryId) {
                    $query->where('template_category_id', $templateCategoryId);
                    return;
                }

                $query->where('category_key', $categoryKey)
                    ->orWhere('category', $categoryLabel);
            });

        $grade = $gradeQuery->first();

        if (! $grade) {
            $grade = new MonthlyGrade([
                'student_id' => $studentId,
                'course_offering_id' => $offering->id,
                'gradebook_month_id' => $monthId,
            ]);
        }

        $grade->fill([
            'category_key' => $categoryKey,
            'template_category_id' => $templateCategoryId,
            'category' => $categoryLabel,
            'score' => $score,
            'max_score' => $maxScore,
            'graded_by' => $gradedByUserId,
        ]);

        $grade->save();

        return $grade;
    }

    private function resolveTemplateCategoryId(
        CourseOffering $offering,
        ?GradebookMonth $month,
        string $categoryKey
    ): ?int {
        $academicYearId = $month?->academic_year_id ?? $offering->academic_year_id;
        $termId = $month?->term_id ?? $offering->term_id;
        $gradeId = $offering->classSection?->grade_id;
        $subjectId = $offering->subject_id;

        if (! $academicYearId || ! $termId || ! $gradeId || ! $subjectId || $categoryKey === '') {
            return null;
        }

        $templateCategoryId = MonthlyCategoryMapping::query()
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('grade_id', $gradeId)
            ->where('subject_id', $subjectId)
            ->where('category_key', $categoryKey)
            ->value('template_category_id');

        if (! $templateCategoryId) {
            Log::warning('Monthly grade mapping missing', [
                'course_offering_id' => $offering->id,
                'gradebook_month_id' => $month?->id,
                'academic_year_id' => $academicYearId,
                'term_id' => $termId,
                'grade_id' => $gradeId,
                'subject_id' => $subjectId,
                'category_key' => $categoryKey,
            ]);
        }

        return $templateCategoryId ? (int) $templateCategoryId : null;
    }
}
