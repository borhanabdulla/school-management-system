<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Actions\ApplyDiscountAction;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Exceptions\DiscountCreatesOverpaymentException;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DiscountOverpaymentTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private Grade $grade;
    private Student $student;
    private Guardian $sponsor;
    private CreateInvoiceAction $createInvoiceAction;
    private ApplyDiscountAction $applyDiscountAction;
    private Discount $discount;
    private \App\Domains\Shared\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $applyPermission = Permission::firstOrCreate(['name' => 'finance.apply_discount']);
        $recordPermission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $this->user = \App\Domains\Shared\Models\User::factory()->create();
        $this->user->givePermissionTo([$applyPermission, $recordPermission]);
        $this->actingAs($this->user);

        $this->year = AcademicYear::factory()->create(['status' => 'active']);
        $this->grade = Grade::factory()->create();
        $this->student = Student::factory()->create(['current_grade_id' => $this->grade->id]);
        $this->sponsor = Guardian::factory()->create();
        $this->student->guardians()->attach($this->sponsor->id, ['is_financial_sponsor' => true, 'relationship' => 'father']);

        $feeType = FeeType::create(['name' => 'Tuition', 'is_active' => true]);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
            'fee_type_id' => $feeType->id,
            'amount' => 1000.00,
            'due_date' => now(),
        ]);

        $this->discount = Discount::create(['name' => 'Promo', 'type' => 'fixed', 'value' => 200]);

        $this->createInvoiceAction = app(CreateInvoiceAction::class);
        $this->applyDiscountAction = app(ApplyDiscountAction::class);
    }

    /** @test */
    public function it_prevents_discount_if_it_makes_paid_amount_greather_than_net_total()
    {
        // 1. Create Invoice (1000)
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));
        $item = $invoice->items->first();

        // 2. Pay 900
        $paymentAction = app(RecordPaymentAction::class);
        $paymentAction->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $this->sponsor->id,
            'amount' => 900,
            'method' => PaymentMethod::Cash,
        ]));

        // 3. Try Apply Discount 200
        // New Net (Potential) = 1000 - 200 = 800
        // Paid (900) > Net (800) -> Overpayment!

        $this->expectException(DiscountCreatesOverpaymentException::class);

        $this->applyDiscountAction->execute($item->id, $this->discount->id);
    }

    /** @test */
    public function it_allows_discount_if_paid_amount_is_still_covered()
    {
        // 1. Create Invoice (1000) - Pay 700
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));
        $item = $invoice->items->first();

        $paymentAction = app(RecordPaymentAction::class);
        $paymentAction->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $this->sponsor->id,
            'amount' => 700,
            'method' => PaymentMethod::Cash,
        ]));

        // 2. Apply Discount 200 -> Net 800
        // Paid (700) < Net (800) -> OK
        $this->applyDiscountAction->execute($item->id, $this->discount->id);

        $invoice->refresh();
        expect($invoice->total_amount)->toEqual(800.00); // 1000 - 200
    }
}
