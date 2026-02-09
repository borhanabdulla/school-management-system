<?php

namespace App\Livewire\Payroll;

use App\Domains\HR\Payroll\Models\PayrollRecord;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PayslipViewer extends Component
{
    public PayrollRecord $record;

    public function mount(int $id)
    {
        $this->record = PayrollRecord::with([
            'batch',
            'staff',
            'contract',
            'items' => fn($q) => $q->with('source')->orderBy('type')->orderBy('category'),
        ])->findOrFail($id);
    }

    public function getEarningsProperty()
    {
        return $this->record->items->where('type', \App\Domains\HR\Payroll\Enums\PayrollItemType::Earning);
    }

    public function getDeductionsProperty()
    {
        return $this->record->items->where('type', \App\Domains\HR\Payroll\Enums\PayrollItemType::Deduction);
    }

    public function render()
    {
        return view('livewire.payroll.payslip-viewer');
    }
}
