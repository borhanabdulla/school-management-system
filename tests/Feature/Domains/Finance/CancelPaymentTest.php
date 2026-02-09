<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Actions\CancelPaymentAction;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Enums\PaymentStatus;
use App\Domains\Finance\Events\PaymentCancelled;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoiceItem;
use App\Domains\Finance\Models\Payment;
use App\Domains\Shared\Models\User;
use Database\Seeders\EducationalStructureSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CancelPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        school()->invalidateYear();
        $this->seed([EducationalStructureSeeder::class, RoleSeeder::class]);
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);

        // Grant permission securely
        $this->admin->givePermissionTo('finance.cancel_payment');
    }

    /** @test */
    public function it_can_cancel_a_posted_payment_and_recalculate_invoice_and_dispatch_event()
    {
        Event::fake([PaymentCancelled::class]);

        // 1. Arrange: Invoice with full payment
        $invoice = Invoice::factory()->create();

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'is_tuition' => true,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'fee_type_id' => $feeType->id,
            'label' => 'Tuition',
            'amount' => 1000,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'status' => PaymentStatus::Posted,
        ]);

        // Ensure initial state
        app(\App\Domains\Finance\Services\InvoiceTotalsService::class)->recalculate($invoice);

        // 2. Act
        $action = app(CancelPaymentAction::class);
        $action->execute($payment->id, 'Mistake entry');

        // 3. Assert
        $payment->refresh();
        $this->assertEquals(PaymentStatus::Cancelled, $payment->status);

        $invoice->refresh();
        $this->assertEquals(0, $invoice->paid_amount);
        $this->assertEquals(InvoiceStatus::Unpaid, $invoice->status);

        Event::assertDispatched(PaymentCancelled::class);
    }

    /** @test */
    public function it_cannot_cancel_if_user_lacks_permission()
    {
        $unauthorizedUser = User::factory()->create();
        $this->actingAs($unauthorizedUser);

        $payment = Payment::factory()->create();

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        app(CancelPaymentAction::class)->execute($payment->id, 'Reason');
    }

    /** @test */
    public function it_cannot_cancel_if_academic_year_is_locked()
    {
        // 1. Lock the year
        $year = AcademicYear::factory()->create(['financial_status' => 'closed']);
        $invoice = Invoice::factory()->create(['academic_year_id' => $year->id]);
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id, 'status' => PaymentStatus::Posted]);

        app(CancelPaymentAction::class)->execute($payment->id, 'Reason');

        $this->assertEquals(PaymentStatus::Cancelled, $payment->fresh()->status);
    }

    /** @test */
    public function it_cannot_cancel_an_already_cancelled_payment()
    {
        $payment = Payment::factory()->create(['status' => PaymentStatus::Cancelled]);

        $this->expectException(ValidationException::class);

        app(CancelPaymentAction::class)->execute($payment->id, 'Again');
    }
}
