<?php

namespace App\Livewire\Payroll;

use App\Domains\HR\Payroll\Models\Loan;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Payroll\Models\LoanInstallment;
use App\Domains\HR\Payroll\Enums\LoanStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class LoanManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public $showCreateModal = false;
    public $staff_id;
    public $amount;
    public $installments_count = 1;
    public $monthly_installment;
    public $start_date;
    public $reason;

    public function mount()
    {
        $this->start_date = now()->addMonth()->startOfMonth()->format('Y-m-d');
    }

    public function updatedAmount()
    {
        $this->calculateInstallment();
    }

    public function updatedInstallmentsCount()
    {
        $this->calculateInstallment();
    }

    public function calculateInstallment()
    {
        if (is_numeric($this->amount) && is_numeric($this->installments_count) && $this->installments_count > 0) {
            $this->monthly_installment = number_format($this->amount / $this->installments_count, 2, '.', '');
        }
    }

    public function create()
    {
        $this->validate([
            'staff_id' => 'required|exists:staff,id',
            'amount' => 'required|numeric|min:1',
            'installments_count' => 'required|integer|min:1|max:60',
            'start_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () {
            $amount = (float) $this->amount;
            $count = (int) $this->installments_count;
            $baseInstallment = round($amount / $count, 2);
            $lastInstallment = round($amount - ($baseInstallment * ($count - 1)), 2);

            $loan = Loan::create([
                'staff_id' => $this->staff_id,
                'amount' => $amount,
                'installments_count' => $count,
                'monthly_installment' => $baseInstallment,
                'start_date' => $this->start_date,
                'reason' => $this->reason,
                'status' => LoanStatus::Approved,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Generate Installments
            $startDate = Carbon::parse($this->start_date);
            for ($i = 0; $i < $count; $i++) {
                $installmentAmount = $i === ($count - 1) ? $lastInstallment : $baseInstallment;
                LoanInstallment::create([
                    'loan_id' => $loan->id,
                    'amount' => $installmentAmount,
                    'due_date' => $startDate->copy()->addMonths($i),
                    'status' => 'pending',
                ]);
            }
        });

        $this->reset(['staff_id', 'amount', 'installments_count', 'monthly_installment', 'reason', 'showCreateModal']);
        $this->dispatch('notify', message: 'تم إنشاء السلفة بنجاح', type: 'success');
    }

    public function render()
    {
        $loans = Loan::with('staff')
            ->when($this->search, function ($q) {
                $q->whereHas('staff', function ($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.payroll.loan-manager', [
            'loans' => $loans,
            'staffList' => Staff::orderBy('first_name')->get(),
        ]);
    }
}
