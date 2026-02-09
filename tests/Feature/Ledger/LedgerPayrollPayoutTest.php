<?php

namespace Tests\Feature\Ledger;

use App\Domains\Finance\Ledger\Data\LedgerEntryData;
use App\Domains\Finance\Ledger\Enums\LedgerCategory;
use App\Domains\Finance\Ledger\Enums\LedgerDirection;
use App\Domains\Finance\Ledger\Enums\LedgerStatus;
use App\Domains\Finance\Ledger\Models\LedgerEntry;
use App\Domains\Finance\Ledger\Services\LedgerService;
use App\Domains\HR\Payroll\Actions\ApprovePayrollAction;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Actions\MarkPayrollPaidAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Enums\PayoutMethod;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerPayrollPayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LedgerService $ledgerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        $this->admin = User::factory()->create();
        $this->ledgerService = app(LedgerService::class);
    }

    /** @test */
    public function it_records_payroll_payout_to_ledger_when_batch_is_paid()
    {
        // Arrange: Create payroll batch
        $staff = Staff::factory()->create();
        Contract::factory()->create([
            'staff_id' => $staff->id,
            'start_date' => now()->startOfYear(),
            'basic_salary' => 5000,
        ]);

        $generateAction = app(GeneratePayrollAction::class);
        $data = PayrollGenerationData::fromYearMonth(now()->year, now()->month);
        $batch = $generateAction->execute($data, $this->admin->id);

        // Move to Approved
        $batch->update(['status' => PayrollBatchStatus::Frozen]);
        app(ApprovePayrollAction::class)->execute($batch, $this->admin->id);

        // Act: Pay the batch (this should trigger the event and record to ledger)
        $payAction = app(MarkPayrollPaidAction::class);
        $paidBatch = $payAction->execute($batch, $this->admin->id, PayoutMethod::Cash);

        // Assert: Ledger entry was created
        $entry = LedgerEntry::where('external_key', "payroll_batch:{$paidBatch->id}")->first();

        $this->assertNotNull($entry, 'Ledger entry should be created');
        $this->assertEquals(LedgerDirection::Out, $entry->direction);
        $this->assertEquals(LedgerCategory::PayrollPayout, $entry->category);
        $this->assertEquals(LedgerStatus::Posted, $entry->status);
        $this->assertEquals($paidBatch->total_net, $entry->amount);
        $this->assertEquals($this->admin->id, $entry->created_by);
    }

    /** @test */
    public function it_does_not_duplicate_ledger_entry_on_repeated_event()
    {
        // Arrange: Create entry manually
        $externalKey = 'payroll_batch:999';

        $data = new LedgerEntryData(
            entryDate: now(),
            direction: LedgerDirection::Out,
            amount: 5000.00,
            category: LedgerCategory::PayrollPayout,
            externalKey: $externalKey,
            sourceType: 'App\Domains\HR\Payroll\Models\PayrollBatch',
            sourceId: 999,
            createdBy: $this->admin->id,
        );

        // Act: Record twice
        $first = $this->ledgerService->record($data);
        $second = $this->ledgerService->record($data);

        // Assert: Only one entry exists
        $this->assertNotNull($first);
        $this->assertNull($second, 'Second record should return null (duplicate)');
        $this->assertEquals(1, LedgerEntry::where('external_key', $externalKey)->count());
    }
}
