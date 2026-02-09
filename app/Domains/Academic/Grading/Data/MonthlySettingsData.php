<?php

namespace App\Domains\Academic\Grading\Data;

final class MonthlySettingsData
{
    public function __construct(
        public readonly array $categories,
        public readonly int $attendanceDeductAfter,
        public readonly float $attendanceDeductPerAbsence,
        public readonly float $attendanceMaxScore,
        public readonly bool $allowCustomCategories
    ) {
    }
}
