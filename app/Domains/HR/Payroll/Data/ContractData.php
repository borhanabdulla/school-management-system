<?php

namespace App\Domains\HR\Payroll\Data;

use Carbon\Carbon;

/**
 * ContractData - بيانات إنشاء عقد جديد
 */
readonly class ContractData
{
    public function __construct(
        public int $staff_id,
        public Carbon $start_date,
        public Carbon $end_date,
        public float $basic_salary,
        public array $allowances = [],
        public ?int $academic_year_id = null,
        public ?string $notes = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            staff_id: $data['staff_id'],
            start_date: Carbon::parse($data['start_date']),
            end_date: Carbon::parse($data['end_date']),
            basic_salary: (float) $data['basic_salary'],
            allowances: $data['allowances'] ?? [],
            academic_year_id: $data['academic_year_id'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
