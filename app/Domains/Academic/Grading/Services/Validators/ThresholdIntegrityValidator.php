<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Validators;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\Health\GradingConfigValidator;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;

final class ThresholdIntegrityValidator implements GradingConfigValidator
{
    /**
     * @return array<int, GradingConfigViolation>
     */
    public function validate(CourseOffering $offering, SubjectGradingConfig $config): array
    {
        $template = $config->template;
        if (! $template) {
            return [];
        }

        $issues = [];

        foreach ($template->categories as $category) {
            if ($category->pass_required) {
                if ($category->pass_threshold === null) {
                    $issues[] = new GradingConfigViolation(
                        'PASS_THRESHOLD_MISSING',
                        sprintf('الفئة %s تتطلب pass_threshold لكن الحقل فارغ.', $category->name),
                        [
                            'category_id' => $category->id,
                            'template_id' => $template->id,
                        ]
                    );
                    continue;
                }
            }

            if ($category->pass_threshold !== null) {
                $threshold = (float) $category->pass_threshold;
                if ($threshold < 0 || $threshold > 100) {
                    $issues[] = new GradingConfigViolation(
                        'PASS_THRESHOLD_OUT_OF_RANGE',
                        sprintf('الفئة %s لديها pass_threshold = %.2f خارج النطاق 0..100.', $category->name, $threshold),
                        [
                            'category_id' => $category->id,
                            'template_id' => $template->id,
                            'threshold' => $threshold,
                        ]
                    );
                }
            }
        }

        return $issues;
    }
}
