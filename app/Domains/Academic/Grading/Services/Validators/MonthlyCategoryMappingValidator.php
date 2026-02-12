<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Validators;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\Health\GradingConfigValidator;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;

final class MonthlyCategoryMappingValidator implements GradingConfigValidator
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $mappingCache = [];

    /**
     * @var array<int, GradebookSettings|null>
     */
    private array $settingsCache = [];

    /**
     * @return array<int, GradingConfigViolation>
     */
    public function validate(CourseOffering $offering, SubjectGradingConfig $config): array
    {
        $issues = [];

        $academicYearId = $offering->academic_year_id ?? null;
        $termId = $offering->term_id ?? null;
        $gradeId = $offering->classSection?->grade_id;
        $subjectId = $offering->subject_id ?? null;

        if (!$academicYearId || !$termId || !$gradeId || !$subjectId) {
            return [
                new GradingConfigViolation(
                    'MAPPING_SCOPE_INCOMPLETE',
                    'بيانات الشعبة/الترم/السنة غير مكتملة لتعريف مابينغ الدفتر الشهري.',
                    [
                        'course_offering_id' => $offering->id,
                        'academic_year_id' => $academicYearId,
                        'term_id' => $termId,
                        'grade_id' => $gradeId,
                        'subject_id' => $subjectId,
                    ],
                    GradingConfigViolation::SEVERITY_WARNING
                ),
            ];
        }

        if (!array_key_exists($academicYearId, $this->settingsCache)) {
            $this->settingsCache[$academicYearId] = GradebookSettings::where('academic_year_id', $academicYearId)->first();
        }
        $settings = $this->settingsCache[$academicYearId];

        if (!$settings) {
            return [
                new GradingConfigViolation(
                    'GRADEBOOK_SETTINGS_MISSING',
                    'لا توجد إعدادات دفتر شهري للسنة المحددة.',
                    ['academic_year_id' => $academicYearId]
                ),
            ];
        }

        $categories = GradebookSettings::normalizeMonthlyCategories($settings->monthly_categories ?? []);
        $categoryKeys = array_values(array_filter(array_map(
            fn($category) => $category['key'] ?? null,
            $categories
        )));

        if ($categoryKeys === []) {
            return [
                new GradingConfigViolation(
                    'MONTHLY_CATEGORIES_EMPTY',
                    'قائمة بنود الدفتر الشهري فارغة.',
                    ['academic_year_id' => $academicYearId]
                ),
            ];
        }

        $cacheKey = "{$academicYearId}_{$termId}_{$gradeId}_{$subjectId}";
        if (!array_key_exists($cacheKey, $this->mappingCache)) {
            $this->mappingCache[$cacheKey] = MonthlyCategoryMapping::query()
                ->where('academic_year_id', $academicYearId)
                ->where('term_id', $termId)
                ->where('grade_id', $gradeId)
                ->where('subject_id', $subjectId)
                ->pluck('category_key')
                ->all();
        }

        $mappedKeys = $this->mappingCache[$cacheKey];
        $missing = array_values(array_diff($categoryKeys, $mappedKeys));

        if ($missing !== []) {
            $issues[] = new GradingConfigViolation(
                'MONTHLY_CATEGORY_MAPPING_MISSING',
                'يوجد بنود شهرية بدون مابينغ إلى فئات القالب.',
                [
                    'missing_count' => count($missing),
                    'missing_keys' => array_slice($missing, 0, 10),
                ]
            );
        }

        return $issues;
    }
}
