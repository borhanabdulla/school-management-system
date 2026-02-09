<?php

namespace Tests\Feature\Journeys;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Shared\Models\User;
use Database\Seeders\EducationalStructureSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Actions\CancelPaymentAction;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Enums\PaymentMethod;

class FinancePromotionHealingTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private $student;
    private $yearA;
    private $invoice;
    private $promotion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        school()->invalidateYear();
        $this->seed([EducationalStructureSeeder::class, RoleSeeder::class]);

        $this->accountant = User::factory()->create();
        $this->accountant->givePermissionTo(['finance.record_payment', 'finance.cancel_payment']);

        $this->yearA = AcademicYear::factory()->create(['name' => '2023-2024', 'status' => 'closed']);
        $this->student = \App\Domains\Academic\Student\Models\Student::factory()->create();

        // 1. Create Debt in Year A
        $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
        $this->invoice = Invoice::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->yearA->id,
            'payer_guardian_id' => $guardian->id, // Critical for PaymentData
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        \App\Domains\Finance\Models\InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'label' => 'Tuition',
            'amount' => 1000,
            'fee_type_id' => \App\Domains\Finance\Models\FeeType::create(['name' => 'F1', 'amount' => 1000])->id
        ]);

        // 2. Create Promotion Record (BLOCKED)
        $this->promotion = Promotion::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->yearA->id,
            'from_grade_id' => 1,
            'to_grade_id' => 2,
            'type' => 'promoted',
            'has_financial_clearance' => false,
            'certificate_blocked' => true,
            'processed_by' => $this->accountant->id,
            'processed_at' => now(),
        ]);
    }

    /** @test */
    public function late_payment_unblocks_certificate_automatically()
    {
        // Assert initial state
        $this->assertTrue($this->promotion->refresh()->certificate_blocked);

        // Authenticate
        $this->actingAs($this->accountant);

        // ACT: Pay the invoice full
        app(RecordPaymentAction::class)->execute(
            PaymentData::fromArray([
                'invoice_id' => $this->invoice->id,
                'guardian_id' => $this->invoice->payer_guardian_id,
                'amount' => 1000,
                'method' => PaymentMethod::Cash,
            ])
        );

        // ASSERT: Promotion updated
        $this->assertFalse($this->promotion->refresh()->certificate_blocked, 'Certificate should be unblocked after payment');
        $this->assertTrue($this->promotion->has_financial_clearance);
    }

    /** @test */
    public function cancelling_payment_reblocks_certificate()
    {
        // Authenticate
        $this->actingAs($this->accountant);

        // 1. Pay first (Unblock)
        $payment = app(RecordPaymentAction::class)->execute(
            PaymentData::fromArray([
                'invoice_id' => $this->invoice->id,
                'guardian_id' => $this->invoice->payer_guardian_id,
                'amount' => 1000,
                'method' => PaymentMethod::Cash,
            ])
        );

        // Ensure clean state
        $this->assertFalse($this->promotion->refresh()->certificate_blocked);

        // 2. Cancel Payment
        app(CancelPaymentAction::class)->execute($payment->id, 'Mistake');

        // ASSERT: Promotion BLOCKED again
        $this->assertTrue($this->promotion->refresh()->certificate_blocked, 'Certificate should be blocked after cancelling payment');
        $this->assertFalse($this->promotion->has_financial_clearance);
    }
}
