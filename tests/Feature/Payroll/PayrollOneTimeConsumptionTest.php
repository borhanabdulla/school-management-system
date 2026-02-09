<?php

namespace Tests\Feature\Payroll;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PayrollOneTimeConsumptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_does_not_consume_one_time_items(): void
    {
        $periodStart = Carbon::create(2025, 10, 1);
        AcademicYear::factory()->active()->create([
            'start_date' => $periodStart->copy()->subMonths(2),
            'end_date' => $periodStart->copy()->addMonths(8),
        ]);

        $contract = Contract::factory()->create([
            'start_date' => $periodStart->copy()->startOfMonth(),
            'end_date' => $periodStart->copy()->endOfMonth(),
        ]);

        $item = ContractItem::create([
            'contract_id' => $contract->id,
            'name' => 'بدل لمرة واحدة',
            'amount' => 250,
            'type' => 'allowance',
            'is_one_time' => true,
            'consumed_at' => null,
        ]);

        $user = User::factory()->create();
        $data = PayrollGenerationData::fromYearMonth(2025, 10);

        app(GeneratePayrollAction::class)->execute($data, $user->id);

        $this->assertNull($item->fresh()->consumed_at);
    }
}
