<?php

namespace App\Domains\Academic\Student\Data;

class StudentDirectoryFilterData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int $academicYearId = null,
        public readonly ?int $gradeId = null,
        public readonly ?int $sectionId = null,
        public readonly ?string $status = null,
        public readonly ?string $financialStatus = null,
        public readonly int $perPage = 15,
        public readonly string $sortBy = 'students.created_at',
        public readonly string $sortDirection = 'desc'
    ) {
    }

    public static function fromArray(array $input): self
    {
        return new self(
            search: $input['search'] ?? null,
            academicYearId: $input['academic_year_id'] ?? null,
            gradeId: $input['grade_id'] ?? null,
            sectionId: $input['section_id'] ?? null,
            status: $input['status'] ?? null,
            financialStatus: $input['financial_status'] ?? null,
            perPage: (int) ($input['per_page'] ?? 15),
            sortBy: $input['sort_by'] ?? 'students.created_at',
            sortDirection: $input['sort_direction'] ?? 'desc',
        );
    }

    public function filters(): array
    {
        return [
            'search' => $this->search,
            'academic_year_id' => $this->academicYearId,
            'grade_id' => $this->gradeId,
            'section_id' => $this->sectionId,
            'status' => $this->status,
            'financial_status' => $this->financialStatus,
        ];
    }
}
