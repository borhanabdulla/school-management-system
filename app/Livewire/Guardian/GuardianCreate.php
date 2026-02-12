<?php

namespace App\Livewire\Guardian;

use App\Livewire\Forms\Guardian\GuardianForm;
use App\Domains\Academic\Student\Actions\CreateGuardianAction;
use Livewire\Component;
use Livewire\Attributes\Layout;

class GuardianCreate extends Component
{
    public GuardianForm $form;

    public function mount()
    {
        abort_unless(auth()->user()->can('guardians.create'), 403, 'ليس لديك صلاحية إضافة أولياء أمور.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.guardian.guardian-create');
    }

    public function save(CreateGuardianAction $action)
    {
        $this->validate();

        try {
            $action->execute($this->form->toServiceArray());

            $this->dispatch('notify', message: 'تم إضافة ولي الأمر بنجاح', type: 'success');
            return redirect()->route('guardians.index');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }
}
