<?php

namespace App\Domains\Academic\Grading\Data;

final class TemplateData
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $academicYearId,
        public readonly ?int $gradeId,
        public readonly ?int $termId
    ) {
    }
}
