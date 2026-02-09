<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Validators;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\Health\GradingConfigValidator;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;

final class TermConsistencyValidator implements GradingConfigValidator
{
    /**
     * @return array<int, GradingConfigViolation>
     */
    public function validate(CourseOffering $offering, SubjectGradingConfig $config): array
    {
        $issues = [];

        if ($offering->term_id !== $config->term_id) {
            $issues[] = new GradingConfigViolation(
                'TERM_MISMATCH',
                'الترم في CourseOffering يختلف عن الترم في SubjectGradingConfig.',
                [
                    'course_offering_term' => $offering->term_id,
                    'config_term' => $config->term_id,
                ]
            );
        }

        $offeringGrade = $offering->classSection?->grade_id;

        if ($offeringGrade !== $config->grade_id) {
            $issues[] = new GradingConfigViolation(
                'GRADE_MISMATCH',
                'الصف في CourseOffering لا يتطابق مع الصف في SubjectGradingConfig.',
                [
                    'course_offering_grade' => $offeringGrade,
                    'config_grade' => $config->grade_id,
                ]
            );
        }

        return $issues;
    }
}
