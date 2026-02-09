<?php

namespace App\Livewire\HR\Leave;

use App\Domains\HR\Leave\Models\LeaveType;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;

class LeaveTypeManager extends Component
{
    use WithPagination;

    public bool $showModal = false;
    #[Locked]
    public ?int $editingId = null;

    public $form = [
        'name' => '',
        'days_per_year' => 30,
        'excludes_holidays' => false,
        'requires_proof' => false,
        'is_paid' => true,
        'is_active' => true,
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.days_per_year' => 'required|integer|min:0',
        'form.excludes_holidays' => 'boolean',
        'form.requires_proof' => 'boolean',
        'form.is_paid' => 'boolean',
        'form.is_active' => 'boolean',
    ];

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->editingId = $id;
        $type = LeaveType::find($id);
        $this->form = $type->only(['name', 'days_per_year', 'excludes_holidays', 'requires_proof', 'is_paid', 'is_active']);
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $type = LeaveType::find($this->editingId);
            $type->update($this->form);
        } else {
            LeaveType::create($this->form);
        }

        \App\Domains\HR\Leave\Services\LeaveLookupService::clearLeaveTypesCache();

        $this->showModal = false;
        $this->resetForm();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'name' => '',
            'days_per_year' => 30,
            'excludes_holidays' => false,
            'requires_proof' => false,
            'is_paid' => true,
            'is_active' => true,
        ];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.hr.leave.leave-type-manager', [
            'types' => LeaveType::paginate(10),
        ])->layout('layouts.app');
    }
    public function recalculateBalances(\App\Domains\HR\Leave\Services\LeaveService $leaveService)
    {
        $result = $leaveService->recalculateBalances();

        $this->dispatch(
            'notify',
            message: "تمت إعادة الاحتساب بنجاح. (تمت معالجة {$result['processed']} موظف، وتصحيح {$result['fixed']})",
            type: 'success'
        );
    }
}
