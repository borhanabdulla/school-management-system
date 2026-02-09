<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Enums\InvoiceStatus;

#[Layout('layouts.app')]
class InvoiceList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function render()
    {
        $invoices = Invoice::with(['student', 'academicYear'])
            ->when($this->search, function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('first_name_ar', 'like', '%' . $this->search . '%')
                        ->orWhere('family_name_ar', 'like', '%' . $this->search . '%')
                        ->orWhere('first_name_en', 'like', '%' . $this->search . '%')
                        ->orWhere('family_name_en', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                // Assuming status filter sends value string
                $query->where('status', InvoiceStatus::from($this->statusFilter));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.finance.invoice-list', [
            'invoices' => $invoices
        ]);
    }
}