<?php

namespace App\Domains\Academic\Attendance\Actions;

use App\Data\Attendance\AttendanceSettingsData;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;

class UpdateAttendanceSettingsAction
{
    /**
     * تحديث الإعدادات مع تطبيق قواعد العمل
     */
    public function execute(AttendanceSetting $settings, AttendanceSettingsData $data): AttendanceSetting
    {
        $settings->update([
            'mode' => $data->mode->value,
            'responsible_role' => $data->responsible_role->value,
            'late_tolerance' => $data->late_tolerance,
        ]);

        return $settings;
    }
}
