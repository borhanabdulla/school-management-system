<?php

namespace Database\Factories;

use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollBatchFactory extends Factory
{
    protected $model = PayrollBatch::class;

    public function definition(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        return [
            'name' => 'Batch ' . $start->format('Y-m'),
            'period_start' => $start,
            'period_end' => $end,
            'year' => (int) $start->year,
            'month' => (int) $start->month,
            'status' => PayrollBatchStatus::Draft,
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'employees_count' => 0,
        ];
    }
}
