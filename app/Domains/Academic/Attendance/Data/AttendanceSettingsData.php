<?php

namespace App\Domains\Academic\Attendance\Data;

use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
use App\Infrastructure\Data\BaseData;

/**
 * DTO for Attendance Settings
 * 
 * @extends BaseData
 */
class AttendanceSettingsData extends BaseData
{
    public function __construct(
        public readonly int $academic_year_id,
        public readonly AttendanceMode $mode,
        public readonly AttendanceResponsibility $responsible_role,
        public readonly int $late_tolerance = 15,
    ) {
    }
}
