<?php

namespace App\Livewire\Payroll;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Payroll\Models\PayrollItem;
use Livewire\Component;

class StaffFinancialProfile extends Component
{
    public Staff $staff;

    public function mount(Staff $staff)
    {
        $this->staff = $staff;
    }

    public function render()
    {
        // Use loaded contracts collection
        $activeContract = $this->staff->contracts
            ->where('status', 'active')
            ->sortByDesc('start_date')
            ->first();

        $contractHistory = $this->staff->contracts
            ->where('id', '!=', $activeContract?->id)
            ->sortByDesc('start_date');

        // Use loaded payrolls collection
        $payrolls = $this->staff->payrollRecords
            ->sortByDesc('created_at');

        // Use loaded loans collection
        $loans = $this->staff->loans
            ->sortByDesc('created_at');

        // Variations (Keep as query for now as it's across all items)
        $variations = PayrollItem::whereHas('record', function ($q) {
            $q->where('staff_id', $this->staff->id);
        })->whereIn('type', ['deduction', 'allowance'])->where('is_one_time', true)->latest()->take(10)->get();

        return view('livewire.payroll.staff-financial-profile', [
            'activeContract' => $activeContract,
            'contractHistory' => $contractHistory,
            'payrolls' => $payrolls,
            'loans' => $loans,
            'variations' => $variations,
        ]);
    }
}
