<?php

namespace App\Livewire\Forms\Attendance;

use App\Domains\Academic\Attendance\Data\AttendanceSettingsData;
use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use Livewire\Form;
use Livewire\Attributes\Validate;

class AttendanceSettingsForm extends Form
{
    public ?AttendanceSetting $settings = null;

    #[Validate('required|exists:academic_years,id')]
    public $academic_year_id;

    #[Validate('required|string')]
    public $mode = 'checkpoints';

    #[Validate('required|string')]
    public $responsible_role = 'subject_teacher';

    #[Validate('required|integer|min:0|max:60')]
    public $late_tolerance = 15;

    public function setSettings(AttendanceSetting $settings)
    {
        $this->settings = $settings;
        $this->academic_year_id = $settings->academic_year_id;
        $this->mode = $settings->mode;
        $this->responsible_role = $settings->responsible_role;
        $this->late_tolerance = $settings->late_tolerance;
    }

    public function toData(): AttendanceSettingsData
    {
        return new AttendanceSettingsData(
            academic_year_id: $this->academic_year_id,
            mode: AttendanceMode::from($this->mode),
            responsible_role: AttendanceResponsibility::from($this->responsible_role),
            late_tolerance: $this->late_tolerance,
        );
    }
}
