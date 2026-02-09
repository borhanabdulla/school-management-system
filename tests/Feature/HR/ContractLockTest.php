<?php

namespace Tests\Feature\HR;

use Tests\TestCase;
use App\Domains\HR\Payroll\Services\ContractService;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Exceptions\PeriodLockedException;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class ContractLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_contract_in_locked_period_is_blocked(): void
    {
        $staff = Staff::factory()->create();

        $start = Carbon::now()->startOfMonth()->addDay();
        $end = $start->copy()->addMonths(1)->endOfMonth();

        PayrollBatch::factory()->create([
            'period_start' => $start->copy()->startOfMonth(),
            'period_end' => $start->copy()->endOfMonth(),
            'year' => (int) $start->year,
            'month' => (int) $start->month,
            'status' => PayrollBatchStatus::Approved,
        ]);

        $service = app(ContractService::class);

        $this->expectException(PeriodLockedException::class);

        $service->createContract([
            'staff_id' => $staff->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'basic_salary' => 5000,
            'status' => 'active',
        ]);
    }
}
