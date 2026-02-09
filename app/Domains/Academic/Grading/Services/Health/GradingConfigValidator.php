<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Health;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;

interface GradingConfigValidator
{
    /**
     * @return array<int, GradingConfigViolation>
     */
    public function validate(CourseOffering $offering, SubjectGradingConfig $config): array;
}
