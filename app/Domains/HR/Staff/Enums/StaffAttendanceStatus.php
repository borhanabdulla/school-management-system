<?php

namespace App\Domains\HR\Staff\Enums;

enum StaffAttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';
}
