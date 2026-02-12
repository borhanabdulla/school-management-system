<?php

namespace Tests\Feature\Payroll;

use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Enums\LoanStatus;
use App\Domains\HR\Payroll\Models\Loan;
use App\Domains\HR\Payroll\Models\LoanInstallment;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PayrollEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Staff $staff;
    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedActiveAcademicYearForDate(now());
        // Setup Admin
        $this->admin = User::factory()->create();

        // Setup Staff
        $this->staff = Staff::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Setup Contract
        $this->contract = Contract::factory()->create([
            'staff_id' => $this->staff->id,
            'basic_salary' => 5000,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => 'active',
        ]);

        // Add Contract Items
        $this->contract->contractItems()->create([
            'name' => 'Housing Allowance',
            'amount' => 1000,
            'type' => 'allowance', // Will be mapped to Earning in logic
            'is_one_time' => false,
        ]);

        $this->contract->contractItems()->create([
            'name' => 'Transport Allowance',
            'amount' => 500,
            'type' => 'allowance',
            'is_one_time' => false,
        ]);
    }

    #[Test]
    public function it_generates_payroll_batch_successfully()
    {
        // Arrange
        $action = app(GeneratePayrollAction::class);
        $data = PayrollGenerationData::fromYearMonth(now()->year, now()->month);

        // Act
        $batch = $action->execute($data, $this->admin->id);

        // Assert
        $this->assertInstanceOf(PayrollBatch::class, $batch);
        $this->assertEquals(PayrollBatchStatus::Draft, $batch->status);
        $this->assertEquals(1, $batch->employees_count);

        // Assert Totals
        // Basic: 5000 + Housing: 1000 + Transport: 500 = 6500
        $this->assertEquals(6500, $batch->total_gross);
        $this->assertEquals(0, $batch->total_deductions);
        $this->assertEquals(6500, $batch->total_net);

        // Assert Record
        $record = $batch->records()->first();
        $this->assertNotNull($record);
        $this->assertEquals($this->staff->id, $record->staff_id);
        $this->assertEquals(5000, $record->basic_salary);
        $this->assertEquals(6500, $record->gross_earnings);
    }

    #[Test]
    public function it_calculates_deductions_correctly()
    {
        $loan = Loan::create([
            'staff_id' => $this->staff->id,
            'amount' => 1000,
            'paid_amount' => 0,
            'installments_count' => 1,
            'monthly_installment' => 200,
            'reason' => 'Test loan',
            'status' => LoanStatus::Approved,
            'start_date' => now()->startOfMonth(),
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        LoanInstallment::create([
            'loan_id' => $loan->id,
            'amount' => 200,
            'due_date' => now()->startOfMonth(),
            'status' => 'pending',
        ]);

        $action = app(GeneratePayrollAction::class);
        $data = PayrollGenerationData::fromYearMonth(now()->year, now()->month);

        $batch = $action->execute($data, $this->admin->id);

        $this->assertEquals(200, $batch->total_deductions);

        $record = $batch->records()->first();
        $this->assertNotNull($record);
        $this->assertEquals(200, $record->total_deductions);
        $this->assertCount(1, $record->deductions);
        $this->assertEquals(200, $record->deductions->first()->amount);
    }

    #[Test]
    public function it_cannot_regenerate_batch_for_same_period()
    {
        // Arrange
        $action = app(GeneratePayrollAction::class);
        $data = PayrollGenerationData::fromYearMonth(now()->year, now()->month);

        $action->execute($data, $this->admin->id);

        // Act & Assert
        $this->expectException(\App\Domains\HR\Payroll\Exceptions\DuplicatePayrollException::class);
        $action->execute($data, $this->admin->id);
    }
}
