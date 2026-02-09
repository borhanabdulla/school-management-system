<?php

namespace Tests\Feature\Payroll;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollBatchUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_batch_per_year_month_is_enforced(): void
    {
        $batch = PayrollBatch::factory()->create([
            'year' => 2025,
            'month' => 10,
        ]);

        $this->expectException(QueryException::class);

        PayrollBatch::factory()->create([
            'year' => $batch->year,
            'month' => $batch->month,
        ]);
    }
}
