<?php

namespace App\Domains\Academic\Grading\Data;

use Illuminate\Support\Collection;

final class GradingSettingsViewData
{
    /**
     * @param Collection<int, mixed> $templates
     * @param Collection<int, mixed> $academicYears
     * @param Collection<int, mixed> $grades
     * @param Collection<int, mixed> $terms
     * @param Collection<int, mixed> $subjects
     * @param array<int, array<string, int|string>> $monthlyMappingStatus
     * @param array<string, int> $monthlyMappingSummary
     */
    public function __construct(
        public readonly Collection $templates,
        public readonly Collection $academicYears,
        public readonly Collection $grades,
        public readonly Collection $terms,
        public readonly Collection $subjects,
        public readonly array $monthlyMappingStatus = [],
        public readonly array $monthlyMappingSummary = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'templates' => $this->templates,
            'academicYears' => $this->academicYears,
            'grades' => $this->grades,
            'terms' => $this->terms,
            'subjects' => $this->subjects,
            'monthlyMappingStatus' => $this->monthlyMappingStatus,
            'monthlyMappingSummary' => $this->monthlyMappingSummary,
        ];
    }
}
