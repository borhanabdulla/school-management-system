<?php

namespace Tests\Feature\Payroll;

use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Exceptions\PayrollFreezeException;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Models\PayrollRecord;
use App\Domains\HR\Payroll\Models\PayrollItem;
use App\Domains\HR\Payroll\Enums\PayrollItemType;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollWorkflowGuardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Staff $staff;
    private Contract $contract;
    private PayrollBatch $batch;
    private PayrollRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedActiveAcademicYearForDate(now());
        $this->admin = User::factory()->create();
        $this->staff = Staff::factory()->create();

        // Create Contract
        $this->contract = Contract::factory()->create([
            'staff_id' => $this->staff->id,
            'basic_salary' => 5000,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => \App\Domains\HR\Payroll\Enums\ContractStatus::Active,
        ]);

        // Generate Batch (Draft)
        $action = app(GeneratePayrollAction::class);
        $data = PayrollGenerationData::fromYearMonth(now()->year, now()->month);
        $this->batch = $action->execute($data, $this->admin->id);

        $this->record = $this->batch->records()->first();
    }

    /** @test */
    public function it_updates_totals_automatically_when_item_is_added()
    {
        $initialNet = $this->record->net_payable;
        $bonusAmount = 1000;

        // Add Bonus Item directly
        PayrollItem::create([
            'payroll_record_id' => $this->record->id,
            'type' => PayrollItemType::Earning,
            'category' => 'bonus',
            'name' => 'Test Bonus',
            'description' => 'Test Bonus',
            'amount' => $bonusAmount,
            'is_manual_override' => true,
        ]);

        // Refresh record
        $this->record->refresh();

        // Net should increase by bonus amount
        $this->assertEquals($initialNet + $bonusAmount, $this->record->net_payable);

        // Batch total should also update (optional, but requested source of truth)
        $this->batch->refresh();
        $this->assertEquals($initialNet + $bonusAmount, $this->batch->total_net);
    }

    /** @test */
    public function it_updates_totals_automatically_when_item_is_deleted()
    {
        // Add item first
        $item = PayrollItem::create([
            'payroll_record_id' => $this->record->id,
            'type' => PayrollItemType::Earning,
            'category' => 'bonus',
            'name' => 'Bonus to Delete',
            'description' => 'Bonus to Delete',
            'amount' => 500,
            'is_manual_override' => true,
        ]);

        $this->record->refresh();
        $netAfterAdd = $this->record->net_payable;

        // Delete item
        $item->delete();

        $this->record->refresh();
        $this->assertLessThan($netAfterAdd, $this->record->net_payable);
    }

    /** @test */
    public function it_prevents_modification_when_batch_is_frozen()
    {
        // Freeze Batch
        $this->batch->update(['status' => PayrollBatchStatus::Frozen]);

        $this->expectException(PayrollFreezeException::class);

        // Try to add item
        PayrollItem::create([
            'payroll_record_id' => $this->record->id,
            'type' => PayrollItemType::Earning,
            'name' => 'Illegal Bonus',
            'amount' => 1000,
        ]);
    }

    /** @test */
    public function it_prevents_modification_of_existing_item_when_frozen()
    {
        // Freeze Batch
        $this->batch->update(['status' => PayrollBatchStatus::Frozen]);

        $item = $this->record->items->first();

        $this->expectException(PayrollFreezeException::class);

        // Try to update amount
        $item->update(['amount' => 9999]);
    }
}
