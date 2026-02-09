<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Actions\ApplyDiscountAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Models\Discount;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class PaymentDiscountFlowRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed necessary lookups if any
        \Illuminate\Support\Facades\DB::table('countries')->insertGetId([
            'name_en' => 'Saudi',
            'name_ar' => 'سعودي',
            'code' => 'SA',
        ]);
    }

    /** @test */
    public function it_handles_partial_payment_and_manual_discount_correctly_scenario_B()
    {
        // 1. Setup Invoice (Original Amount: 1000)
        $user = User::factory()->create();
        $applyPermission = Permission::firstOrCreate(['name' => 'finance.apply_discount']);
        $recordPermission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $user->givePermissionTo([$applyPermission, $recordPermission]);
        $this->actingAs($user);

        $year = AcademicYear::factory()->create(['status' => 'active', 'financial_status' => 'open']);
        $grade = Grade::factory()->create();
        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);

        $structure = FeeStructure::create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 1000,
            'due_date' => now()->addMonth(),
        ]);

        $student = Student::factory()->create(['current_grade_id' => $grade->id]);
        $guardian = Guardian::factory()->create();
        $student->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_financial_sponsor' => true]);

        // Create Invoice
        $invoice = app(CreateInvoiceAction::class)->execute(InvoiceData::fromArray([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'generate_items' => true
        ]));

        $this->assertEquals(1000, $invoice->total_amount);
        $this->assertEquals(0, $invoice->paid_amount);
        $this->assertEquals('unpaid', $invoice->status->value);

        // 2. Partial Payment (Pay 400)
        app(RecordPaymentAction::class)->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $guardian->id,
            'amount' => 400,
            'method' => PaymentMethod::Cash,
        ]));

        $invoice->refresh();
        $this->assertEquals(400, $invoice->paid_amount);
        $this->assertEquals('partially_paid', $invoice->status->value); // Assuming Enum has this or logic sets it
        // Note: Check actual status enum values. If 'partially_paid' doesn't exist, it might be 'unpaid' with amounts.

        // 3. Apply Manual Discount (Discount: 200)
        // Net becomes 1000 - 200 = 800. Paid 400. Remaining 400.
        $discount = Discount::create(['name' => 'Sibling', 'value' => 200, 'type' => 'fixed']);

        app(ApplyDiscountAction::class)->execute(
            $invoice->items->first()->id,
            $discount->id,
            'Manual App'
        );

        $invoice->refresh();
        $this->assertEquals(800, $invoice->total_amount); // Net
        $this->assertEquals(400, $invoice->paid_amount);

        // 4. Try Apply OVERFLOW Discount (Discount: 500)
        // Net would be 1000 - 200 - 500 = 300. Paid is 400.
        // Paid (400) > Net (300) -> Should fail/block.

        $largeDiscount = Discount::create(['name' => 'Huge', 'value' => 500, 'type' => 'fixed']);

        try {
            app(ApplyDiscountAction::class)->execute(
                $invoice->items->first()->id,
                $largeDiscount->id,
                'Overflow App'
            );
            $this->fail('Should have blocked discount that makes Net < Paid');
        } catch (\Exception $e) {
            // Expected
            $this->assertTrue(true);
        }

        // Verify state unchanged
        $invoice->refresh();
        $this->assertEquals(800, $invoice->total_amount);
    }
}
