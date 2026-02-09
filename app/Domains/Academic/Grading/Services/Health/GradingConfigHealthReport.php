<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services\Health;

final class GradingConfigHealthReport
{
    /**
     * @var array<int, array{term_id: int, course_offering_id: int, subject_id: int|null, grade_id: int|null}>
     */
    private array $missing = [];

    /**
     * @var array<int, array{term_id: int, course_offering_id: int, subject_id: int|null, grade_id: int|null, violation: array}>
     */
    private array $invalid = [];

    /**
     * @var array<int, array{term_id: int, course_offering_id: int, subject_id: int|null, grade_id: int|null, violation: array}>
     */
    private array $warnings = [];

    private int $checked = 0;

    public function incrementChecked(int $value = 1): void
    {
        $this->checked += $value;
    }

    /**
     * @param array{term_id: int, course_offering_id: int, subject_id: int|null, grade_id: int|null} $payload
     */
    public function recordMissing(array $payload): void
    {
        $this->missing[] = $payload;
    }

    public function recordInvalid(array $payload, GradingConfigViolation $violation): void
    {
        $this->invalid[] = $this->buildRow($payload, $violation);
    }

    public function recordWarning(array $payload, GradingConfigViolation $violation): void
    {
        $this->warnings[] = $this->buildRow($payload, $violation);
    }

    private function buildRow(array $payload, GradingConfigViolation $violation): array
    {
        return [
            ...$payload,
            'violation' => $violation->toArray(),
        ];
    }

    public function toArray(): array
    {
        return [
            'checked' => $this->checked,
            'missing' => $this->missing,
            'invalid' => $this->invalid,
            'warnings' => $this->warnings,
        ];
    }
}
