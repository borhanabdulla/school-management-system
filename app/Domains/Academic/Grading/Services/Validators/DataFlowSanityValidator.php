<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Validators;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\Health\GradingConfigValidator;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;

final class DataFlowSanityValidator implements GradingConfigValidator
{
    /**
     * @return array<int, GradingConfigViolation>
     */
    public function validate(CourseOffering $offering, SubjectGradingConfig $config): array
    {
        $issues = [];
        $template = $config->template;

        if (! $template) {
            return $issues;
        }

        if ($template->categories->isEmpty()) {
            $issues[] = new GradingConfigViolation(
                'TEMPLATE_NO_CATEGORIES',
                'القالب لا يحتوي على فئات.',
                ['template_id' => $template->id]
            );
        }

        if ((float) $config->max_score <= 0) {
            $issues[] = new GradingConfigViolation(
                'MAX_SCORE_INVALID',
                'أقصى درجة يجب أن تكون أكبر من صفر.',
                ['max_score' => (float) $config->max_score]
            );
        }

        if ((float) $config->pass_score <= 0) {
            $issues[] = new GradingConfigViolation(
                'PASS_SCORE_INVALID',
                'درجة النجاح يجب أن تكون أكبر من صفر.',
                ['pass_score' => (float) $config->pass_score]
            );
        }

        if ($offering->term_id === null) {
            $issues[] = new GradingConfigViolation(
                'OFFERING_NO_TERM',
                'CourseOffering لا يحتوي على term_id.',
                ['course_offering_id' => $offering->id],
                GradingConfigViolation::SEVERITY_WARNING
            );
        }

        return $issues;
    }
}
