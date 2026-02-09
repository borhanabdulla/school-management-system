<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Actions\GrantFinancialExceptionAction;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Services\StudentFinancialClearanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FinancialClearanceTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private Grade $grade;
    private Student $student;
    private Guardian $sponsor;
    private StudentFinancialClearanceService $clearanceService;
    private CreateInvoiceAction $createInvoiceAction;
    private \App\Domains\Shared\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $this->user = \App\Domains\Shared\Models\User::factory()->create();
        $this->user->givePermissionTo($permission);
        $this->actingAs($this->user);

        $this->year = AcademicYear::factory()->create(['status' => 'active']);
        $this->grade = Grade::factory()->create();
        $this->student = Student::factory()->create(['current_grade_id' => $this->grade->id]);
        $this->sponsor = Guardian::factory()->create();
        $this->student->guardians()->attach($this->sponsor->id, ['is_financial_sponsor' => true, 'relationship' => 'father']);

        // Fees
        $feeType = FeeType::create(['name' => 'Tuition', 'is_active' => true]);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
            'fee_type_id' => $feeType->id,
            'amount' => 1000.00,
            'due_date' => now(),
        ]);

        $this->clearanceService = app(StudentFinancialClearanceService::class);
        $this->createInvoiceAction = app(CreateInvoiceAction::class);
    }

    /** @test */
    public function it_identifies_student_as_not_cleared_if_outstanding_balance_exists()
    {
        // 1. Create Invoice (1000)
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        // 2. Pay 800 (Outstanding 200)
        $paymentAction = app(RecordPaymentAction::class);
        $paymentAction->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $this->sponsor->id,
            'amount' => 800,
            'method' => PaymentMethod::Cash,
        ]));

        // 3. Check Clearance
        $result = $this->clearanceService->checkClearance($this->student->id, $this->year->id);

        expect($result['is_cleared'])->toBeFalse();
        expect($this->clearanceService->getOutstandingBalance($this->student->id, $this->year->id))->toEqual(200.00);
    }

    /** @test */
    public function it_identifies_student_as_cleared_if_fully_paid()
    {
        // 1. Create Invoice
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        // 2. Pay 1000
        $paymentAction = app(RecordPaymentAction::class);
        $paymentAction->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $this->sponsor->id,
            'amount' => 1000,
            'method' => PaymentMethod::Cash,
        ]));

        // 3. Check Clearance
        $result = $this->clearanceService->checkClearance($this->student->id, $this->year->id);
        expect($result['is_cleared'])->toBeTrue();
    }

    /** @test */
    public function it_grants_exception_override_for_uncleared_student()
    {
        // 1. Create Invoice (Unpaid)
        $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        expect($this->clearanceService->checkClearance($this->student->id, $this->year->id)['is_cleared'])->toBeFalse();

        // 2. Grant Exception
        $grantAction = app(GrantFinancialExceptionAction::class);
        $user = \App\Domains\Shared\Models\User::factory()->create();
        $grantAction->execute($this->student->id, $this->year->id, 'Managers Order', $user->id);

        // 3. Check Clearance -> Should be Cleared now
        $result = $this->clearanceService->checkClearance($this->student->id, $this->year->id);

        expect($result['is_cleared'])->toBeTrue();
        expect($result['reason'])->toContain('Managers Order');
    }
}
