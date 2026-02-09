<?php

namespace App\Livewire\Payroll;

use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Domains\HR\Payroll\Services\ContractService;
use App\Domains\HR\Payroll\Models\SalaryComponent;
use App\Domains\HR\Payroll\Exceptions\PeriodLockedException;
use App\Domains\HR\Payroll\Services\PayrollPeriodLockService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ContractManager extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $statusFilter = '';

    // Chaining Confirmation
    public bool $showChainingConfirmation = false;
    public bool $confirmedChaining = false;

    // Form
    public bool $showForm = false;
    public ?int $editingId = null;
    public array $form = [
        'staff_id' => '',
        'start_date' => '',
        'end_date' => '',
        'basic_salary' => '',
        'items' => [],
        'notes' => '',
    ];

    // Item Form
    public string $newItemName = '';
    public string $newItemAmount = '';
    public string $newItemType = 'allowance';
    public bool $newItemIsOneTime = false;

    protected function rules(): array
    {
        return [
            'form.staff_id' => 'required|exists:staff,id',
            'form.start_date' => 'required|date',
            'form.end_date' => 'required|date|after:form.start_date',
            'form.basic_salary' => 'required|numeric|min:0',
            'form.notes' => 'nullable|string',
        ];
    }

    public function mount()
    {
        $this->form['start_date'] = now()->startOfMonth()->format('Y-m-d');
        $this->form['end_date'] = now()->addYear()->endOfMonth()->format('Y-m-d');
    }

    // ==================== Actions ====================

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id)
    {
        // $contract = Contract::findOrFail($id);
        $contract = Contract::with('contractItems')->findOrFail($id);

        if ($contract->is_locked) {
            $this->dispatch('error', message: 'هذا العقد مقفل ولا يمكن تعديله.');
            return;
        }

        $this->editingId = $id;
        $this->form = [
            'staff_id' => $contract->staff_id,
            'start_date' => $contract->start_date->format('Y-m-d'),
            'end_date' => $contract->end_date->format('Y-m-d'),
            // 'start_date'   => $contract->start_date->toDateString(),
            // 'end_date'     => $contract->end_date->toDateString(),

            'basic_salary' => $contract->basic_salary,
            'items' => $contract->contractItems->map(fn($item) => [
                'name' => $item->name,
                'amount' => $item->amount,
                'type' => $item->type,
                'is_one_time' => $item->is_one_time,
            ])->toArray(),
            'notes' => $contract->notes,
        ];
        $this->showForm = true;
    }

    // Chaining Confirmation


    public function save(ContractService $contractService, PayrollPeriodLockService $periodLockService)
    {
        $this->validate();

        $data = [
            'staff_id' => $this->form['staff_id'],
            'start_date' => $this->form['start_date'],
            'end_date' => $this->form['end_date'],
            'basic_salary' => $this->form['basic_salary'],
            'notes' => $this->form['notes'],
            'status' => 'active',
        ];

        try {
            if ($this->editingId) {
                $lockStart = \Carbon\Carbon::parse($data['start_date']);
                $lockEnd = \Carbon\Carbon::parse($data['end_date']);
                $lockingBatch = $periodLockService->getLockingBatchForRange($lockStart, $lockEnd);
                if ($lockingBatch) {
                    throw new PeriodLockedException(
                        $lockingBatch->period_start->toDateString(),
                        $lockingBatch->period_end->toDateString(),
                        $lockingBatch->status->value,
                        $lockingBatch->id
                    );
                }

                // Update existing
                $contract = Contract::findOrFail($this->editingId);
                $contract->ensureEditable();
                $contract->update($data);

                // Sync Items
                $contract->contractItems()->delete();
                foreach ($this->form['items'] as $item) {
                    $contract->contractItems()->create($item);
                }

                $this->dispatch('notify', message: 'تم تحديث العقد بنجاح.', type: 'success');
            } else {
                // Check for existing active contract
                if (!$this->confirmedChaining) {
                    $hasActive = Contract::where('staff_id', $this->form['staff_id'])
                        ->where('status', 'active')
                        ->exists();

                    if ($hasActive) {
                        $this->showChainingConfirmation = true;
                        return;
                    }
                }

                // Create new with chaining
                $contractService->createContract($data, $this->form['items']);
                $this->dispatch('notify', message: 'تم إنشاء العقد بنجاح.', type: 'success');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
            return;
        }

        $this->closeForm();
    }

    public function confirmChaining()
    {
        $this->confirmedChaining = true;
        $this->showChainingConfirmation = false;
        $this->save(app(ContractService::class));
    }

    public function cancelChaining()
    {
        $this->showChainingConfirmation = false;
        $this->confirmedChaining = false;
    }

    public function delete(int $id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->is_locked) {
            $this->dispatch('error', message: 'لا يمكن حذف عقد مقفل.');
            return;
        }

        $contract->delete();
        $this->dispatch('notify', message: 'تم حذف العقد.', type: 'success');
    }

    // ==================== Items ====================

    public function addItem()
    {
        if (empty($this->newItemName) || empty($this->newItemAmount)) {
            return;
        }

        $this->form['items'][] = [
            'name' => $this->newItemName,
            'amount' => (float) $this->newItemAmount,
            'type' => $this->newItemType,
            'is_one_time' => $this->newItemIsOneTime,
        ];

        $this->newItemName = '';
        $this->newItemAmount = '';
        $this->newItemType = 'allowance';
        $this->newItemIsOneTime = false;
        $this->newItemComponentId = null;
    }

    public function removeItem(int $index)
    {
        unset($this->form['items'][$index]);
        $this->form['items'] = array_values($this->form['items']);
    }

    // ==================== Helpers ====================

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->form = [
            'staff_id' => '',
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->addYear()->endOfMonth()->format('Y-m-d'),
            'basic_salary' => '',
            'items' => [],
            'notes' => '',
        ];
        $this->newItemName = '';
        $this->newItemAmount = '';
        $this->newItemType = 'allowance';
        $this->newItemIsOneTime = false;
        $this->newItemComponentId = null;
    }

    public function render()
    {
        $contracts = Contract::query()
            ->with('staff')
            ->when($this->search, function ($q) {
                $q->whereHas('staff', function ($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $staffList = Staff::orderBy('first_name')->get();

        return view('livewire.payroll.contract-manager', [
            'contracts' => $contracts,
            'staffList' => $staffList,
            'salaryComponents' => SalaryComponent::where('is_active', true)->get(),
        ]);
    }
}
