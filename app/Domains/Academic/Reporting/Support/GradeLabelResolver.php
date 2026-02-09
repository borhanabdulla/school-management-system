<?php

namespace App\Domains\Academic\Reporting\Support;

use App\Domains\Academic\Grading\Services\GradingCalculatorService;

final class GradeLabelResolver
{
    public static function forPercentage(float $percentage): string
    {
        return app(GradingCalculatorService::class)->resolveGradeLabel($percentage);
    }
}
