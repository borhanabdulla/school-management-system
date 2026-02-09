<?php

namespace App\Livewire\Finance;

use App\Domains\Finance\Models\Invoice;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['items.discountApplications.discount', 'items.feeType', 'student', 'academicYear']);
    }

    public function render()
    {
        return view('livewire.finance.invoice-show');
    }
}
