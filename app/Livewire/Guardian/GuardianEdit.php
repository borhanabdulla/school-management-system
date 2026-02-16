<?php

namespace App\Livewire\Guardian;

use App\Livewire\Forms\Guardian\GuardianForm;
use App\Domains\Academic\Student\Actions\UpdateGuardianAction;
use App\Domains\Academic\Student\Models\Guardian;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('تعديل ولي الأمر')]
class GuardianEdit extends Component
{
    public GuardianForm $form;
    public Guardian $guardian;

    public function mount(Guardian $guardian)
    {
        abort_unless(auth()->user()->can('guardians.edit'), 403, 'ليس لديك صلاحية تعديل أولياء الأمور.');

        $this->guardian = $guardian;
        $this->form->setGuardian($guardian);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.guardian.guardian-edit');
    }

    public function save(UpdateGuardianAction $action)
    {
        $this->validate();

        try {
            $action->execute($this->guardian, $this->form->toServiceArray());

            $this->dispatch('notify', message: 'تم تعديل بيانات ولي الأمر بنجاح', type: 'success');
            return redirect()->route('guardians.show', $this->guardian->id);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }
}
