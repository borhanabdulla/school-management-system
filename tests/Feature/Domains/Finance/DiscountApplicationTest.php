<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Actions\ApplyDiscountAction;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Exceptions\DiscountAlreadyAppliedException;
use App\Domains\Finance\Exceptions\DiscountExceedsItemAmountException;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DiscountApplicationTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private Grade $grade;
    private Student $student;
    private Guardian $sponsor;
    private CreateInvoiceAction $createInvoiceAction;
    private ApplyDiscountAction $applyDiscountAction;
    private Discount $percentageDiscount;
    private Discount $fixedDiscount;
    private FeeStructure $tuitionFee;
    private \App\Domains\Shared\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $applyPermission = Permission::firstOrCreate(['name' => 'finance.apply_discount']);
        $recordPermission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $this->user = \App\Domains\Shared\Models\User::factory()->create();
        $this->user->givePermissionTo([$applyPermission, $recordPermission]);
        $this->actingAs($this->user);

        // 1. Setup Basic Data
        $this->year = AcademicYear::factory()->create(['status' => 'active']);
        $this->grade = Grade::factory()->create();

        $this->student = Student::factory()->create(['current_grade_id' => $this->grade->id]);
        $this->sponsor = Guardian::factory()->create();
        $this->student->guardians()->attach($this->sponsor->id, ['is_financial_sponsor' => true, 'relationship' => 'father']);

        // 2. Setup Fees (1000 SAR)
        $feeType = FeeType::create(['name' => 'Tuition', 'is_active' => true]);
        $this->tuitionFee = FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
            'fee_type_id' => $feeType->id,
            'amount' => 1000.00,
            'due_date' => now()->addMonth(),
        ]);

        // 3. Setup Discounts
        $this->percentageDiscount = Discount::create(['name' => 'Early Bird', 'type' => 'percentage', 'value' => 10]); // 10%
        $this->fixedDiscount = Discount::create(['name' => 'Sibling', 'type' => 'fixed', 'value' => 200]); // 200 SAR

        $this->createInvoiceAction = app(CreateInvoiceAction::class);
        $this->applyDiscountAction = app(ApplyDiscountAction::class);
    }

    /** @test */
    public function it_applies_percentage_discount_correctly()
    {
        // 1. Create Invoice (Total 1000)
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        $item = $invoice->items->first();

        // 2. Apply 10% Discount
        $this->applyDiscountAction->execute($item->id, $this->percentageDiscount->id, 'Early Payment');

        // 3. Verify
        // Discount Amount = 1000 * 10% = 100
        // Net Total = 1000 - 100 = 900
        $invoice->refresh();

        expect($invoice->total_amount)->toEqual(900.00);

        $this->assertDatabaseHas('discount_applications', [
            'invoice_item_id' => $item->id,
            'discount_id' => $this->percentageDiscount->id,
            'applied_amount' => 100.00,
        ]);
    }

    /** @test */
    public function it_applies_fixed_discount_correctly()
    {
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        $item = $invoice->items->first();

        // 2. Apply 200 SAR Discount
        $this->applyDiscountAction->execute($item->id, $this->fixedDiscount->id, 'Sibling Discount');

        // 3. Verify
        // Net Total = 1000 - 200 = 800
        $invoice->refresh();
        expect($invoice->total_amount)->toEqual(800.00);
    }

    /** @test */
    public function it_prevents_stacking_discounts()
    {
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        $item = $invoice->items->first();

        // 1. Apply First Discount
        $this->applyDiscountAction->execute($item->id, $this->fixedDiscount->id);

        // 2. Try Apply Second Discount -> Exception
        $this->expectException(DiscountAlreadyAppliedException::class);
        $this->applyDiscountAction->execute($item->id, $this->percentageDiscount->id);
    }

    /** @test */
    public function it_prevents_discount_exceeding_item_amount()
    {
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        $item = $invoice->items->first();

        // Huge Discount
        $hugeDiscount = Discount::create(['name' => 'Huge', 'type' => 'fixed', 'value' => 1500]);

        $this->expectException(DiscountExceedsItemAmountException::class);
        $this->applyDiscountAction->execute($item->id, $hugeDiscount->id);
    }
    /** @test */
    public function it_updates_invoice_status_to_paid_if_discount_covers_remaining_balance()
    {
        // 1. Create Invoice (Total 1000)
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));
        $item = $invoice->items->first();

        // 2. Pay 800 (Status -> Partial)
        $paymentAction = app(\App\Domains\Finance\Actions\RecordPaymentAction::class);
        $paymentAction->execute(\App\Domains\Finance\Data\PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $this->sponsor->id,
            'amount' => 800,
            'method' => \App\Domains\Finance\Enums\PaymentMethod::Cash,
        ]));

        $invoice->refresh();
        expect($invoice->status)->toBe(\App\Domains\Finance\Enums\InvoiceStatus::PartiallyPaid);
        expect($invoice->paid_amount)->toEqual(800.00);

        // 3. Apply Discount 200 (Net -> 800)
        // Now Net (800) == Paid (800) -> Should become Paid
        $this->applyDiscountAction->execute($item->id, $this->fixedDiscount->id);

        $invoice->refresh();
        expect($invoice->total_amount)->toEqual(800.00);
        expect($invoice->status)->toBe(\App\Domains\Finance\Enums\InvoiceStatus::Paid);
    }

    /** @test */
    public function it_rejects_discount_on_closed_academic_year()
    {
        // 1. Create Invoice
        $invoice = $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));
        $item = $invoice->items->first();

        // 2. Close Year
        $this->year->update(['status' => 'closed']);

        // 3. Try Apply Discount -> Exception
        $this->expectException(\App\Domains\Finance\Exceptions\AcademicYearClosedException::class);
        $this->applyDiscountAction->execute($item->id, $this->percentageDiscount->id);
    }
}
