<?php

namespace App\Domains\Academic\Attendance\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case ABSENT = 'absent';
    case LATE = 'late';
    case EXCUSED = 'excused';
    case PENDING = 'pending';
    case ESCAPED = 'escaped';

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => __('hr.status.present'),
            self::ABSENT => __('hr.status.absent'),
            self::LATE => __('hr.status.late'),
            self::EXCUSED => __('hr.status.excused'),
            self::PENDING => __('hr.status.pending'),
            self::ESCAPED => __('hr.status.escaped'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PRESENT => 'emerald',
            self::ABSENT => 'red',
            self::LATE => 'amber',
            self::EXCUSED => 'blue',
            self::PENDING => 'gray',
            self::ESCAPED => 'orange',
        };
    }
}
