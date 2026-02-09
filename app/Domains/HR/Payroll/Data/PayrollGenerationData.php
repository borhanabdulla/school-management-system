<?php

namespace App\Domains\HR\Payroll\Data;

use Carbon\Carbon;

/**
 * PayrollGenerationData - بيانات توليد مسير الرواتب
 */
readonly class PayrollGenerationData
{
    public function __construct(
        public int $year,
        public int $month,
        public Carbon $period_start,
        public Carbon $period_end,
        public ?string $name = null,
        public ?string $notes = null,
    ) {
    }

    public static function fromYearMonth(int $year, int $month): self
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return new self(
            year: $year,
            month: $month,
            period_start: $start,
            period_end: $end,
            name: "مسير رواتب " . $start->translatedFormat('F Y'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            year: $data['year'],
            month: $data['month'],
            period_start: Carbon::parse($data['period_start']),
            period_end: Carbon::parse($data['period_end']),
            name: $data['name'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
