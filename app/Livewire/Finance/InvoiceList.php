<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;

#[Layout('layouts.app')]
class InvoiceList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $yearFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedYearFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Base query with filters applied (reused for both list and stats).
     */
    private function baseQuery()
    {
        return Invoice::query()
            ->when($this->search, function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('first_name_ar', 'like', '%' . $this->search . '%')
                        ->orWhere('family_name_ar', 'like', '%' . $this->search . '%')
                        ->orWhere('first_name_en', 'like', '%' . $this->search . '%')
                        ->orWhere('family_name_en', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', InvoiceStatus::from($this->statusFilter));
            })
            ->when($this->yearFilter, function ($query) {
                $query->where('academic_year_id', $this->yearFilter);
            });
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->baseQuery();

        return [
            'total' => (clone $query)->sum('total_amount'),
            'paid' => (clone $query)->sum('paid_amount'),
            'remaining' => (clone $query)->sum('total_amount') - (clone $query)->sum('paid_amount'),
            'count' => (clone $query)->count(),
            'paid_count' => (clone $query)->where('status', InvoiceStatus::Paid)->count(),
            'outstanding_count' => (clone $query)->outstanding()->count(),
        ];
    }

    public function render()
    {
        $invoices = $this->baseQuery()
            ->with(['student', 'academicYear'])
            ->latest()
            ->paginate(15);

        $academicYears = AcademicYear::orderByDesc('start_date')->get(['id', 'name']);

        return view('livewire.finance.invoice-list', [
            'invoices' => $invoices,
            'academicYears' => $academicYears,
        ]);
    }
}