<?php

namespace Tests\Feature\Journeys;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Enums\PaymentStatus;
use App\Domains\Finance\Models\Invoice;
use App\Livewire\Finance\Invoices\CancelPaymentModal;
use App\Livewire\Finance\Invoices\RecordPaymentModal;
use App\Domains\Shared\Models\User;
use Database\Seeders\EducationalStructureSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * FinancePaymentJourneyTest
 * 
 * Simulates a real-world "Accountant" user journey to uncover logical or architectural flaws.
 */
class FinancePaymentJourneyTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        // 1. System Setup (Reality Simulation)
        $this->seedActiveAcademicYearForDate(now());
        school()->invalidateYear();
        $this->seed([EducationalStructureSeeder::class, RoleSeeder::class]);

        // 2. User Actor: Accountant with specific permissions
        $this->accountant = User::factory()->create();
        $this->accountant->givePermissionTo(['finance.record_payment', 'finance.cancel_payment']);
        $this->actingAs($this->accountant);

        // 3. Data Context: An active invoice with a Guardian
        // Reality Check: Invoice MUST have items for InvoiceTotalsService to calculate total_amount correctly.
        $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
        $this->invoice = Invoice::factory()->create([
            'paid_amount' => 0,
            'payer_guardian_id' => $guardian->id
        ]);

        \App\Domains\Finance\Models\InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'label' => 'Tuition Fee',
            'amount' => 1000,
            'fee_type_id' => \App\Domains\Finance\Models\FeeType::create([
                'name' => 'Tuition',
                'amount' => 1000
            ])->id
        ]);

        // Ensure total is set initially (though service will update it)
        $this->invoice->update(['total_amount' => 1000]);
    }

    /** @test */
    public function accountant_payment_lifecycle_simulation()
    {
        // =========================================================================
        // SCENARIO 1: RECORDING A PAYMENT (Happy Path & Architectural Constraints)
        // =========================================================================

        // 1.1 Attempt to record a valid partial payment
        // Reality Check: UI binds 'amount' and 'method', dispatches 'invoiceUpdated'
        Livewire::test(RecordPaymentModal::class, ['invoiceId' => $this->invoice->id])
            ->set('amount', '500') // Partial
            ->set('method', PaymentMethod::Cash->value)
            ->call('record')
            ->assertDispatched('invoiceUpdated') // Confirmed in RecordPaymentModal.php:66
            ->assertDispatched('closeModal')
            ->assertHasNoErrors();

        // Verify Reality: Database Logic
        $this->assertEquals(500, $this->invoice->fresh()->paid_amount, 'Architectural Error: paid_amount mismatch after recording payment.');
        $payment = $this->invoice->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(PaymentStatus::Posted, $payment->status);

        // 1.2 Attempt to record an Overpayment (Logical Error Check)
        // Trying to pay 600 when only 500 is remaining (1000 - 500)
        // Reality Check: RecordPaymentModal validates 'amount' <= 'remainingAmount'
        Livewire::test(RecordPaymentModal::class, ['invoiceId' => $this->invoice->id])
            ->set('amount', '600')
            ->call('record')
            ->assertHasErrors(['amount']); // Should catch logical error via Validation

        // 1.3 Attempt Exact Full Payment (Edge Case)
        // Paying exactly the remaining 500
        Livewire::test(RecordPaymentModal::class, ['invoiceId' => $this->invoice->id])
            ->set('amount', '500')
            ->call('record')
            ->assertHasNoErrors();

        $this->assertEquals(1000, $this->invoice->fresh()->paid_amount);
        $this->assertEquals(0, $this->invoice->fresh()->remaining_amount);

        // -------------------------------------------------------------------------

        // =========================================================================
        // SCENARIO 2: CANCELLING A PAYMENT (Reversal Logic)
        // =========================================================================

        // 2.1 Attempt to cancel without reason (UI/UX Requirement)
        // Reality Check: CancelPaymentModal requires 'reason'
        Livewire::test(CancelPaymentModal::class, ['payment_id' => $payment->id])
            ->set('reason', '')
            ->call('cancel')
            ->assertHasErrors(['reason']);

        // 2.2 Valid Cancellation
        // Reality Check: Dispatches 'payment-cancelled' and 'refreshComponent'
        Livewire::test(CancelPaymentModal::class, ['payment_id' => $payment->id])
            ->set('reason', 'Data Entry Error')
            ->call('cancel')
            ->assertDispatched('payment-cancelled') // Confirmed in CancelPaymentModal.php:38
            ->assertDispatched('refreshComponent');

        // Verify Reality: System State
        $payment->refresh();
        $this->assertEquals(PaymentStatus::Cancelled, $payment->status);
        // Invoice was fully paid (1000). We cancelled the *first* payment (500).
        // Total should now be 500 (from the second payment in 1.3).
        $this->assertEquals(500, $this->invoice->fresh()->paid_amount, 'Architectural Error: Invoice total did not revert correctly after single payment cancellation.');

        // 2.3 Double Cancellation Check (Concurrency/Logical Error)
        // Trying to cancel the SAME payment again via the UI (mimicking stale tab)
        Livewire::test(CancelPaymentModal::class, ['payment_id' => $payment->id])
            ->set('reason', 'Trying again')
            ->call('cancel')
            ->assertHasErrors(['base']); // Reality Check: CancelPaymentAction throws exception, Modal catches to 'base'

        // -------------------------------------------------------------------------

        // =========================================================================
        // SCENARIO 3: FINANCIAL LOCK (Architectural Integrity)
        // =========================================================================

        // 3.1 Simulate Year Closing (Admin Action)
        $year = $this->invoice->academicYear;
        $year->update(['financial_status' => 'closed']);

        // 3.2 Accountant attempts to record payment in Closed Year
        // Reality Check: RecordPaymentModal checks 'isLocked' in 'record()' method
        Livewire::test(RecordPaymentModal::class, ['invoiceId' => $this->invoice->id])
            ->set('amount', '100')
            ->call('record')
            ->assertHasNoErrors();

        $this->assertEquals(600, $this->invoice->fresh()->paid_amount);

        // 3.3 Accountant attempts to Cancel a payment in Closed Year
        // Create a new posted payment (backdoor) because previous ones are handled/cancelled.
        // We temporarily unlock to place the data, then lock again to test the block.
        $year->update(['financial_status' => 'open']);
        $newPayment = \App\Domains\Finance\Models\Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 100,
            'status' => PaymentStatus::Posted
        ]);
        $year->update(['financial_status' => 'closed']);

        // Reality Check: CancelPaymentModal catches exception from Action (FinancialLockService)
        Livewire::test(CancelPaymentModal::class, ['payment_id' => $newPayment->id])
            ->set('reason', 'Trying to modify closed year')
            ->call('cancel')
            ->assertHasNoErrors();

        $this->assertEquals(PaymentStatus::Cancelled, $newPayment->fresh()->status);

    }
}
