<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Livewire\Forms\Teacher\TeacherForm;
use App\Domains\HR\Teacher\Actions\CreateTeacherAction;
use App\Domains\HR\Teacher\Data\TeacherOnboardingData;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\HR\Teacher\Exceptions\TeacherException;
use Illuminate\Support\Facades\DB;

class TeacherCreate extends Component
{
    use \Livewire\WithFileUploads;

    public TeacherForm $form;

    // Smart Search State
    public $existingSpecializations = [];

    public function mount()
    {
        abort_unless(auth()->user()->can('staff.create'), 403, 'ليس لديك صلاحية إضافة معلمين.');
        $this->form->hire_date = now()->format('Y-m-d');
        $this->loadSpecializations();
    }

    public function loadSpecializations()
    {
        // جلب التخصصات الفريدة والموجودة مسبقاً لتوحيد المسميات
        $this->existingSpecializations = Teacher::whereNotNull('specialization')
            ->distinct()
            ->pluck('specialization')
            ->filter()
            ->values()
            ->toArray();
    }

    public function save(CreateTeacherAction $action)
    {
        $this->createTeacher($action);

        $this->dispatch('notify', message: 'تم إضافة المعلم بنجاح.');
        return redirect()->route('teachers.index');
    }

    public function saveAndCreateAnother(CreateTeacherAction $action)
    {
        $this->createTeacher($action);

        $this->dispatch('notify', message: 'تم إضافة المعلم بنجاح. يمكنك إضافة معلم آخر.');

        // Reset form but keep default values
        $this->form->reset();
        $this->form->hire_date = now()->format('Y-m-d');
        $this->form->max_weekly_classes = 24;

        // Reload specializations in case a new one was added
        $this->loadSpecializations();
    }

    protected function createTeacher($action)
    {
        $this->form->validate();

        try {
            $data = TeacherOnboardingData::fromForm($this->form);
            $action->execute($data);
        } catch (\Exception $e) {
            // Log error if needed
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.teacher.teacher-create', [
            'specializations' => $this->existingSpecializations
        ])->layout('layouts.app');
    }
}
