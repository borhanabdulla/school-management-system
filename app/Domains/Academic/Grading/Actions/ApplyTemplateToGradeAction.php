<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * ApplyTemplateToGradeAction - تطبيق قالب تقييم على صف
 * 
 * يربط قالب التقييم بجميع مواد الصف للفصل المحدد
 */
class ApplyTemplateToGradeAction
{
    /**
     * تطبيق القالب على صف
     *
     * @param GradingTemplate $template القالب
     * @param Grade $grade الصف
     * @param Term $term الفصل الدراسي
     * @param array $options خيارات إضافية
     * @return int عدد المواد المُعدّة
     */
    public function execute(
        GradingTemplate $template,
        Grade $grade,
        Term $term,
        array $options = []
    ): int {
        $maxScore = $options['max_score'] ?? 100;
        $passScore = $options['pass_score'] ?? 50;
        $overwrite = $options['overwrite'] ?? false;

        return DB::transaction(function () use ($template, $grade, $term, $maxScore, $passScore, $overwrite) {
            $count = 0;
            $subjects = $grade->subjects;

            foreach ($subjects as $subject) {
                // تحقق من وجود إعداد سابق
                $existing = SubjectGradingConfig::where([
                    'subject_id' => $subject->id,
                    'grade_id' => $grade->id,
                    'term_id' => $term->id,
                ])->first();

                if ($existing && !$overwrite) {
                    continue;
                }

                SubjectGradingConfig::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'grade_id' => $grade->id,
                        'term_id' => $term->id,
                    ],
                    [
                        'grading_template_id' => $template->id,
                        'max_score' => $maxScore,
                        'pass_score' => $passScore,
                    ]
                );

                $count++;
            }

            return $count;
        });
    }

    /**
     * تطبيق القالب على جميع صفوف مرحلة
     */
    public function executeForStage(
        GradingTemplate $template,
        int $stageId,
        Term $term,
        array $options = []
    ): int {
        $grades = Grade::where('educational_stage_id', $stageId)->get();
        $totalCount = 0;

        foreach ($grades as $grade) {
            $totalCount += $this->execute($template, $grade, $term, $options);
        }

        return $totalCount;
    }
}
