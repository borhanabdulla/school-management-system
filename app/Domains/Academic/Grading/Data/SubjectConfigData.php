<?php

namespace App\Domains\Academic\Grading\Data;

final class SubjectConfigData
{
    public function __construct(
        public readonly int $subjectId,
        public readonly int $gradeId,
        public readonly int $termId,
        public readonly int $templateId
    ) {
    }
}
