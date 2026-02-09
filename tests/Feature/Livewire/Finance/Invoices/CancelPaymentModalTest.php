<?php

namespace Tests\Feature\Livewire\Finance\Invoices;

use App\Domains\Finance\Enums\PaymentStatus;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Livewire\Finance\Invoices\CancelPaymentModal;
use App\Domains\Shared\Models\User;
use Database\Seeders\EducationalStructureSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CancelPaymentModalTest extends TestCase
{
    use RefreshDatabase;

    private \App\Domains\Shared\Models\User $admin;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        school()->invalidateYear();
        $this->seed([EducationalStructureSeeder::class, RoleSeeder::class]);
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('finance.cancel_payment');
        $this->actingAs($this->admin);

        $invoice = Invoice::factory()->create();
        $this->payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'status' => PaymentStatus::Posted,
        ]);
    }

    /** @test */
    public function it_can_cancel_payment()
    {
        Livewire::test(CancelPaymentModal::class, ['payment_id' => $this->payment->id])
            ->assertSet('payment_id', $this->payment->id)
            ->set('reason', 'Duplicate entry')
            ->call('cancel')
            ->assertHasNoErrors()
            ->assertDispatched('payment-cancelled')
            ->assertDispatched('closeModal');

        $this->payment->refresh();
        $this->assertEquals(PaymentStatus::Cancelled, $this->payment->status);
        $this->assertEquals('Duplicate entry', $this->payment->cancel_reason);
    }

    /** @test */
    public function it_requires_cancellation_reason()
    {
        Livewire::test(CancelPaymentModal::class, ['payment_id' => $this->payment->id])
            ->set('reason', '')
            ->call('cancel')
            ->assertHasErrors(['reason' => 'required']);
    }
}
