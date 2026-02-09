<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Exceptions\InvoicePayerMismatchException;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Models\Invoice;
use Database\Factories\Domains\Academic\Student\Models\GuardianFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PayerSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private Grade $grade;
    private Student $student;
    private Guardian $father; // Sponsor
    private Guardian $mother;
    private CreateInvoiceAction $createAction;
    private RecordPaymentAction $paymentAction;
    private \App\Domains\Shared\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $this->user = \App\Domains\Shared\Models\User::factory()->create();
        $this->user->givePermissionTo($permission);
        $this->actingAs($this->user);

        // 1. Setup Basic Data
        $this->year = AcademicYear::factory()->create(['status' => 'active']);
        $this->grade = Grade::factory()->create();

        // 2. Setup Student & Guardians
        $this->student = Student::factory()->create(['current_grade_id' => $this->grade->id]);

        $this->father = Guardian::factory()->create(['first_name' => 'Father']);
        $this->mother = Guardian::factory()->create(['first_name' => 'Mother']);

        // Father is Sponsor initially
        $this->student->guardians()->attach($this->father->id, [
            'relationship' => 'father',
            'is_financial_sponsor' => true
        ]);

        $this->student->guardians()->attach($this->mother->id, [
            'relationship' => 'mother',
            'is_financial_sponsor' => false
        ]);

        // 3. Setup Fees
        $feeType = FeeType::create(['name' => 'Tuition', 'is_active' => true]);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
            'fee_type_id' => $feeType->id,
            'amount' => 1000,
            'due_date' => now()->addMonth(),
        ]);

        $this->createAction = app(CreateInvoiceAction::class);
        $this->paymentAction = app(RecordPaymentAction::class);
    }

    /** @test */
    public function it_snapshots_payer_at_creation_and_ignores_later_changes()
    {
        // 1. Create Invoice
        $invoice = $this->createAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        // Check Snapshot
        expect($invoice->payer_guardian_id)->toBe($this->father->id);

        // 2. Change Sponsor to Mother
        $this->student->guardians()->updateExistingPivot($this->father->id, ['is_financial_sponsor' => false]);
        $this->student->guardians()->updateExistingPivot($this->mother->id, ['is_financial_sponsor' => true]);

        // 3. Verify Invoice Payer is STILL Father
        $invoice->refresh();
        expect($invoice->payer_guardian_id)->toBe($this->father->id);
    }

    /** @test */
    public function it_enforces_payment_eligibility_against_snapshot()
    {
        $invoice = $this->createAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        // Snapshot is Father
        expect($invoice->payer_guardian_id)->toBe($this->father->id);

        // 1. Try Pay by Mother (Should Fail even if we make her sponsor now)
        // Let's make mother sponsor just to prove snapshot override works
        $this->student->guardians()->updateExistingPivot($this->father->id, ['is_financial_sponsor' => false]);
        $this->student->guardians()->updateExistingPivot($this->mother->id, ['is_financial_sponsor' => true]);

        // Trying to pay by Mother...
        $this->expectException(InvoicePayerMismatchException::class);

        $this->paymentAction->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $this->mother->id,
            'amount' => 500,
            'method' => PaymentMethod::Cash,
        ]));
    }

    /** @test */
    public function it_auto_fixes_null_payer_on_first_legitimate_payment()
    {
        // 1. Create Invoice manually to force null payer (simulating old data)
        $invoice = Invoice::create([
            'invoice_number' => 'OLD-001',
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'issue_date' => now(),
            'due_date' => now(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => \App\Domains\Finance\Enums\InvoiceStatus::Unpaid,
            'payer_guardian_id' => null, // NULL
        ]);

        expect($invoice->payer_guardian_id)->toBeNull();

        // 2. Pay by Sponsor (Father)
        $this->paymentAction->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $this->father->id,
            'amount' => 500,
            'method' => PaymentMethod::Cash,
        ]));

        // 3. Verify Payer is now Fixed
        $invoice->refresh();
        expect($invoice->payer_guardian_id)->toBe($this->father->id);
        expect($invoice->payer_set_at)->not->toBeNull();
    }
}
