<?php

namespace Tests\Feature\Payroll;

use App\Domains\HR\Payroll\Actions\ApprovePayrollAction;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Domains\HR\Payroll\Models\Loan;
use App\Domains\HR\Payroll\Models\LoanInstallment;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollApprovalSideEffectsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Staff $staff;
    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        $this->admin = User::factory()->create();
        $this->staff = Staff::factory()->create();

        $this->contract = Contract::factory()->create([
            'staff_id' => $this->staff->id,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(2),
            'basic_salary' => 5000,
            'status' => \App\Domains\HR\Payroll\Enums\ContractStatus::Active,
        ]);
    }

    /** @test */
    public function it_consumes_one_time_allowances_and_pays_loan_installments_upon_approval()
    {
        // 1. Setup Data
        // A. One-time Allowance (Bonus)
        $bonus = ContractItem::create([
            'contract_id' => $this->contract->id,
            'name' => 'Performance Bonus',
            'amount' => 1000,
            'type' => 'allowance',
            'is_one_time' => true,
            'consumed_at' => null,
        ]);

        // B. Loan Installment
        $loan = Loan::create([
            'staff_id' => $this->staff->id,
            'amount' => 12000,
            'paid_amount' => 0,
            'installments_count' => 12,
            'monthly_installment' => 1000,
            'status' => 'approved',
            'start_date' => now()->startOfYear(),
        ]);

        $installment = LoanInstallment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'due_date' => now(), // Should match current payroll period
            'status' => 'pending',
        ]);

        // 2. Generate Payroll
        $action = app(GeneratePayrollAction::class);
        $data = PayrollGenerationData::fromYearMonth(now()->year, now()->month);
        $batch = $action->execute($data, $this->admin->id);

        // Verify items exist in payroll
        $record = $batch->records()->where('staff_id', $this->staff->id)->first();

        $this->assertTrue(
            $record->items->contains('source_id', $bonus->id),
            'Bonus item not found in payroll'
        );

        // Note: Loan source_id might be mapped to installment id or loan id depending on logic
        // In PayrollCalculationService: 'source_type' => LoanInstallment::class, 'source_id' => $installment->id
        $this->assertTrue(
            $record->items->contains('source_id', $installment->id),
            'Loan installment deduction not found in payroll'
        );

        // 3. Freeze & Approve
        $batch->update(['status' => PayrollBatchStatus::Frozen]);

        $approveAction = app(ApprovePayrollAction::class);
        $approveAction->execute($batch, $this->admin->id);

        // 4. Verify Side Effects

        // A. Bonus Consumed
        $bonus->refresh();
        $this->assertNotNull($bonus->consumed_at, 'One-time bonus was not marked as consumed');

        // B. Loan Installment Paid
        $installment->refresh();
        $this->assertEquals('paid', $installment->status, 'Loan installment status not updated to paid');
        $this->assertEquals($batch->id, $installment->payroll_batch_id, 'Loan installment not linked to batch');

        // C. Loan Updated
        $loan->refresh();
        $this->assertEquals(1000, $loan->paid_amount, 'Loan paid amount not updated');
    }
}
