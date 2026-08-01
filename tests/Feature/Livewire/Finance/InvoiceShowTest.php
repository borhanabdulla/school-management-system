<?php

namespace Tests\Feature\Livewire\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Actions\ApplyDiscountAction;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoiceItem;
use App\Livewire\Finance\Invoices\ApplyDiscountModal;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceShowTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private Invoice $invoice;
    private Discount $discount;
    private InvoiceItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Permissions
        $role = Role::create(['name' => 'accountant']);
        $permissionView = Permission::create(['name' => 'finance.view']);
        $permissionDiscount = Permission::create(['name' => 'finance.apply_discount']);
        $role->givePermissionTo($permissionView);
        $role->givePermissionTo($permissionDiscount);

        $this->accountant = User::factory()->create();
        $this->accountant->assignRole($role);
        $this->actingAs($this->accountant);

        // 2. Setup Data
        $year = AcademicYear::factory()->create(['status' => 'active', 'financial_status' => 'open']);
        $feeType = FeeType::create(['name' => 'Tuition']);
        $this->discount = Discount::create(['name' => 'Sibling', 'value' => 100, 'type' => 'fixed', 'is_active' => true]);

        $this->invoice = Invoice::factory()->create(['academic_year_id' => $year->id]);
        $this->item = InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'fee_type_id' => $feeType->id,
            'amount' => 1000
        ]);
    }

    /** @test */
    public function it_renders_invoice_details()
    {
        $this->actingAs($this->accountant)
            ->get(route('finance.invoices.show', $this->invoice))
            ->assertSee($this->invoice->student->first_name)
            ->assertSee(number_format(1000, 2));
    }

    /** @test */
    public function it_can_apply_manual_discount_with_permission()
    {
        Livewire::actingAs($this->accountant)
            ->test(ApplyDiscountModal::class, ['invoice' => $this->invoice])
            ->set('invoice_item_id', $this->item->id)
            ->set('discount_id', $this->discount->id)
            ->set('reason', 'Valid Reason')
            ->call('apply')
            ->assertDispatched('invoiceUpdated')
            ->assertDispatched('notify')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('discount_applications', [
            'invoice_item_id' => $this->item->id,
            'discount_id' => $this->discount->id,
            'reason' => 'Valid Reason',
            'applied_by' => $this->accountant->id
        ]);
    }

    /** @test */
    public function it_prevents_applying_discount_without_reason()
    {
        Livewire::actingAs($this->accountant)
            ->test(ApplyDiscountModal::class, ['invoice' => $this->invoice])
            ->set('invoice_item_id', $this->item->id)
            ->set('discount_id', $this->discount->id)
            ->set('reason', '') // Empty reason
            ->call('apply')
            ->assertHasErrors(['reason']);
    }

    /** @test */
    public function it_shows_error_message_on_logic_failure_overflow()
    {
        // Discount > Item Amount
        $hugeDiscount = Discount::create(['name' => 'Huge', 'value' => 2000, 'type' => 'fixed']);

        Livewire::actingAs($this->accountant)
            ->test(ApplyDiscountModal::class, ['invoice' => $this->invoice])
            ->set('invoice_item_id', $this->item->id)
            ->set('discount_id', $hugeDiscount->id)
            ->set('reason', 'Overflow')
            ->call('apply')
            ->assertHasErrors(['general']); // Should catch implementation exception
    }

    /** @test */
    public function it_disables_apply_on_locked_year()
    {
        // Close the year
        $this->invoice->academicYear->update(['financial_status' => 'closed']);
        $this->invoice->refresh();

        Livewire::actingAs($this->accountant)
            ->test(ApplyDiscountModal::class, ['invoice' => $this->invoice])
            ->assertSee('السنة المالية مغلقة') // Warning exists
            // Try explicit bypass check
            ->set('invoice_item_id', $this->item->id)
            ->set('discount_id', $this->discount->id)
            ->set('reason', 'Bypass')
            ->call('apply')
            ->assertHasErrors(['general']);
    }

    /** @test */
    public function it_does_not_show_items_with_existing_discount()
    {
        // Apply one discount first
        app(ApplyDiscountAction::class)->execute($this->item->id, $this->discount->id, 'First');
        $this->invoice->refresh();

        // Create another item without discount
        $item2 = InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'fee_type_id' => $this->item->fee_type_id,
            'amount' => 500
        ]);

        Livewire::actingAs($this->accountant)
            ->test(ApplyDiscountModal::class, ['invoice' => $this->invoice])
            ->assertSee($item2->feeType->name) // Should see fresh item
            ->assertDontSee($this->item->feeType->name . ' (' . number_format(1000, 2) . ')'); // Should filter out already discounted item
    }
}
