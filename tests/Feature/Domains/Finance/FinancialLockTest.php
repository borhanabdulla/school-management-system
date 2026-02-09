<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Actions\ApplyDiscountAction;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Shared\Models\User;
use App\Domains\Finance\Services\FinancialLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use App\Domains\Academic\Student\Models\Student;

class FinancialLockTest extends TestCase
{
    use RefreshDatabase;

    private FinancialLockService $service;
    private $year;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FinancialLockService::class);
        $this->year = AcademicYear::factory()->create(['status' => 'active', 'financial_status' => 'open']);
        $this->user = User::factory()->create();
        $recordPermission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $applyPermission = Permission::firstOrCreate(['name' => 'finance.apply_discount']);
        $this->user->givePermissionTo([$recordPermission, $applyPermission]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_blocks_fee_structure_modification_when_locked()
    {
        // 1. Create structure while open - OK
        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();

        $fs = FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 1000,
            'due_date' => now(),
        ]);

        // 2. Lock the year
        $this->service->lock($this->year, $this->user);

        // 3. Try to update - Should Fail
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The academic year is financially closed');

        $fs->update(['amount' => 2000]);
    }

    /** @test */
    public function it_blocks_creating_fee_structure_when_locked()
    {
        // Lock first
        $this->service->lock($this->year, $this->user);

        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The academic year is financially closed');

        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 1000,
            'due_date' => now(),
        ]);
    }

    /** @test */
    public function it_blocks_apply_discount_action_when_locked()
    {
        // 1. Setup Invoice while open
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        $student = Student::factory()->create(['current_grade_id' => $grade->id]);
        $this->setupSponsor($student);

        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 1000,
            'due_date' => now(),
        ]);

        $invoice = app(CreateInvoiceAction::class)->execute(InvoiceData::fromArray([
            'student_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
        ]));

        $discount = Discount::create(['name' => 'Test', 'value' => 100, 'type' => 'fixed']);

        // 2. Lock
        $this->service->lock($this->year, $this->user);

        // 3. Try ApplyDiscount - Should Fail
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The academic year is financially closed');

        app(ApplyDiscountAction::class)->execute(
            $invoice->items->first()->id,
            $discount->id,
            'Late Discount'
        );
    }

    /** @test */
    public function it_allows_payments_when_locked()
    {
        // 1. Setup Invoice while open
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        $student = Student::factory()->create(['current_grade_id' => $grade->id]);
        $this->setupSponsor($student);

        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 1000,
            'due_date' => now(),
        ]);

        $invoice = app(CreateInvoiceAction::class)->execute(InvoiceData::fromArray([
            'student_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
        ]));

        // 2. Lock
        $this->service->lock($this->year, $this->user);

        // 3. Try to Pay - Should Succeed
        $invoice->refresh();
        app(RecordPaymentAction::class)->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $invoice->payer_guardian_id,
            'amount' => 500,
            'method' => \App\Domains\Finance\Enums\PaymentMethod::Cash,
        ]));

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500
        ]);
    }

    private function setupSponsor($student)
    {
        $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
        $student->guardians()->attach($guardian->id, [
            'relationship' => 'father',
            'is_financial_sponsor' => true,
            'is_emergency_contact' => true,
        ]);
    }
}
