<?php

namespace Tests\Feature\Livewire\Finance\Invoices;

use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Models\Invoice;
use App\Livewire\Finance\Invoices\RecordPaymentModal;
use App\Domains\Shared\Models\User;
use Database\Seeders\EducationalStructureSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecordPaymentModalTest extends TestCase
{
    use RefreshDatabase;

    private \App\Domains\Shared\Models\User $admin;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        school()->invalidateYear();
        $this->seed([EducationalStructureSeeder::class, RoleSeeder::class]);
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('finance.record_payment');
        $this->actingAs($this->admin);

        // Create Guardian and link to Invoice
        $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
        $this->invoice = Invoice::factory()->create([
            'total_amount' => 1000,
            'paid_amount' => 0,
            'payer_guardian_id' => $guardian->id
        ]);
    }

    /** @test */
    public function it_can_open_and_record_payment()
    {
        Livewire::test(RecordPaymentModal::class, ['invoiceId' => $this->invoice->id])
            ->assertSet('invoice_id', $this->invoice->id)
            ->set('amount', '500')
            ->set('method', PaymentMethod::Cash->value)
            ->set('notes', 'Partial payment')
            ->call('record')
            ->assertDispatched('invoiceUpdated')
            ->assertDispatched('closeModal');

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'amount' => 500,
            'method' => PaymentMethod::Cash->value,
            'notes' => 'Partial payment',
        ]);

        $this->assertEquals(500, $this->invoice->fresh()->paid_amount);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Livewire::test(RecordPaymentModal::class, ['invoiceId' => $this->invoice->id])
            ->set('amount', '')
            ->call('record')
            ->assertHasErrors(['amount' => 'required']);
    }

    /** @test */
    public function it_validates_amount_does_not_exceed_remaining()
    {
        Livewire::test(RecordPaymentModal::class, ['invoiceId' => $this->invoice->id])
            ->set('amount', '2000') // More than 1000
            ->call('record')
            ->assertHasErrors(['amount']);
    }
}
