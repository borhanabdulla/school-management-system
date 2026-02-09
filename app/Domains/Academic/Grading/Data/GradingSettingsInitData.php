<?php

namespace App\Domains\Academic\Grading\Data;

final class GradingSettingsInitData
{
    public function __construct(
        public readonly GeneralSettingsData $generalSettings,
        public readonly GradeScaleData $gradeScale,
        public readonly ?int $defaultGradeId,
        public readonly ?int $activeTermId,
        public readonly ?int $firstTemplateId,
        public readonly ?MonthlySettingsData $monthlySettings,
        public readonly array $subjectConfigs
    ) {
    }
}
