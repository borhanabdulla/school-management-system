<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Data;

use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Student\Models\StudentMark;

final class GradeAmendmentData
{
    public function __construct(
        public readonly float $oldScore,
        public readonly float $newScore,
        public readonly string $reason,
        public readonly int $actorId,
        public readonly ?int $termId,
        public readonly ?int $courseOfferingId
    ) {
    }

    public static function forStudentMark(
        StudentMark $mark,
        float $newScore,
        string $reason,
        int $actorId
    ): self {
        return new self(
            oldScore: (float) ($mark->scaled_score ?? 0),
            newScore: $newScore,
            reason: $reason,
            actorId: $actorId,
            termId: $mark->term_id,
            courseOfferingId: $mark->course_offering_id
        );
    }

    public static function forMonthlyGrade(
        MonthlyGrade $grade,
        float $newScore,
        string $reason,
        int $actorId
    ): self {
        return new self(
            oldScore: (float) ($grade->score ?? 0),
            newScore: $newScore,
            reason: $reason,
            actorId: $actorId,
            termId: $grade->gradebookMonth?->term_id,
            courseOfferingId: $grade->course_offering_id
        );
    }
}
