<?php

namespace App\Livewire\Finance\Invoices;

use Livewire\Component;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\InvoiceItem;
use App\Domains\Finance\Actions\ApplyDiscountAction;
use Illuminate\Validation\ValidationException;

class ApplyDiscountModal extends Component
{
    public $invoice_id;
    public $invoice_item_id;
    public $discount_id;
    public $reason;

    public function mount(Invoice $invoice)
    {
        $this->invoice_id = $invoice->id;
    }

    public function render()
    {
        $invoice = Invoice::findOrFail($this->invoice_id);

        // Filter Items: Only items that DON'T have a discount (basic stacking rule for now)
        $items = $invoice->items->filter(function ($item) {
            return $item->discountApplications->isEmpty();
        });

        // Filter Discounts: Only active
        $discounts = Discount::active()->get();

        $isLocked = $invoice->academicYear->financial_status === 'closed';

        return view('livewire.finance.invoices.apply-discount-modal', [
            'items' => $items,
            'discounts' => $discounts,
            'isLocked' => $isLocked,
        ]);
    }

    public function closeModal()
    {
        $this->dispatch('close-modal');
    }

    public function apply()
    {
        $this->validate([
            'invoice_item_id' => 'required|exists:invoice_items,id',
            'discount_id' => 'required|exists:discounts,id',
            'reason' => 'required|string|min:3',
        ]);

        $invoice = Invoice::findOrFail($this->invoice_id);

        // Guard: Financial Lock (Double Check)
        if ($invoice->academicYear->financial_status === 'closed') {
            $this->addError('general', 'لا يمكن تطبيق خصم على سنة مالية مغلقة.');
            return;
        }

        try {
            app(ApplyDiscountAction::class)->execute(
                $this->invoice_item_id,
                $this->discount_id,
                $this->reason
            );

            $this->dispatch('invoiceUpdated');
            $this->closeModal();
            $this->dispatch('notify', message: 'تم تطبيق الخصم بنجاح');

        } catch (ValidationException $e) {
            $this->addError('general', $e->getMessage());
        } catch (\Exception $e) {
            $this->addError('general', 'حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }
}