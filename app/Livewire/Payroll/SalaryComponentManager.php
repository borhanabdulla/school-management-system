<?php

namespace App\Livewire\Payroll;

use App\Domains\HR\Payroll\Actions\CreateSalaryComponentAction;
use App\Domains\HR\Payroll\Actions\UpdateSalaryComponentAction;
use App\Domains\HR\Payroll\Actions\DeleteSalaryComponentAction;
use App\Domains\HR\Payroll\Models\SalaryComponent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SalaryComponentManager extends Component
{
    use WithPagination;

    public $name;
    public $type = 'allowance';
    public $is_percentage = false;
    public $percentage_value;
    public $fixed_value;
    public $is_active = true;
    public $editingId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:allowance,deduction',
        'is_percentage' => 'boolean',
        'percentage_value' => 'nullable|numeric|min:0|max:100|required_if:is_percentage,true',
        'fixed_value' => 'nullable|numeric|min:0|required_if:is_percentage,false',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.payroll.salary-component-manager', [
            'components' => SalaryComponent::latest()->paginate(10),
        ]);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'is_percentage' => $this->is_percentage,
            'percentage_value' => $this->is_percentage ? $this->percentage_value : null,
            'fixed_value' => $this->is_percentage ? null : $this->fixed_value,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $component = SalaryComponent::findOrFail($this->editingId);
            app(UpdateSalaryComponentAction::class)->execute($component, $data);
            $this->dispatch('notify', 'تم تحديث البند بنجاح');
        } else {
            app(CreateSalaryComponentAction::class)->execute($data);
            $this->dispatch('notify', 'تم إضافة البند بنجاح');
        }

        $this->resetForm();
    }

    public function edit($id)
    {
        $component = SalaryComponent::find($id);
        $this->editingId = $component->id;
        $this->name = $component->name;
        $this->type = $component->type;
        $this->is_percentage = $component->is_percentage;
        $this->percentage_value = $component->percentage_value;
        $this->fixed_value = $component->fixed_value;
        $this->is_active = $component->is_active;
    }

    public function delete($id)
    {
        $component = SalaryComponent::findOrFail($id);
        app(DeleteSalaryComponentAction::class)->execute($component);
        $this->dispatch('notify', 'تم حذف البند بنجاح');
    }

    public function resetForm()
    {
        $this->reset(['name', 'type', 'is_percentage', 'percentage_value', 'fixed_value', 'is_active', 'editingId']);
    }

    public function toggleType()
    {
        // Reset values when switching type if needed, or keep them.
    }
}
