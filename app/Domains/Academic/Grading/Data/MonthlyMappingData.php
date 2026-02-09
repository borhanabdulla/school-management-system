<?php

namespace App\Domains\Academic\Grading\Data;

final class MonthlyMappingData
{
    public function __construct(
        public readonly int $academicYearId,
        public readonly int $termId,
        public readonly int $gradeId,
        public readonly int $subjectId,
        public readonly array $mappings
    ) {
    }
}
