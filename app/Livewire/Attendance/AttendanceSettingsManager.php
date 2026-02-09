<?php

namespace App\Livewire\Attendance;

use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
use App\Livewire\Forms\Attendance\AttendanceSettingsForm;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Services\AttendanceSettingsService;
use App\Domains\Academic\Attendance\Actions\UpdateAttendanceSettingsAction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class AttendanceSettingsManager extends Component
{
    public AttendanceSettingsForm $form;

    protected AttendanceSettingsService $service;

    public function boot(AttendanceSettingsService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        // الحصول على السنة النشطة
        $activeYear = school()->activeYear();

        if ($activeYear) {
            $settings = $this->service->getSettings($activeYear->id);
            $this->form->setSettings($settings);
        } else {
            // التعامل مع حالة عدم وجود سنة نشطة (نادر)
            $this->dispatch('error', message: 'لا توجد سنة دراسية نشطة حالياً.');
        }
    }

    #[Computed]
    public function modes()
    {
        return AttendanceMode::cases();
    }

    #[Computed]
    public function roles()
    {
        return AttendanceResponsibility::cases();
    }

    public $showConfirmationModal = false;
    public $pendingSettings = null;

    public function save()
    {
        $this->form->validate();

        // التحقق مما إذا كان النمط قد تغير وهناك سجلات سابقة
        if (
            $this->form->settings->mode !== $this->form->mode &&
            $this->service->hasExistingAttendanceRecords($this->form->settings->academic_year_id)
        ) {
            $this->showConfirmationModal = true;
            return;
        }

        $this->performSave();
    }

    public function confirmSave()
    {
        $this->performSave();
        $this->showConfirmationModal = false;
    }

    protected function performSave()
    {
        try {
            app(UpdateAttendanceSettingsAction::class)->execute($this->form->settings, $this->form->toData());
            $this->dispatch('notify', message: 'تم حفظ إعدادات الحضور بنجاح');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.attendance.attendance-settings-manager');
    }
}
