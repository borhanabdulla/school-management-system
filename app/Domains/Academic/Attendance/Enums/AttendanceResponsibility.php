<?php

namespace App\Domains\Academic\Attendance\Enums;

enum AttendanceResponsibility: string
{
    case HomeroomTeacher = 'class_teacher';
    case SubjectTeacher = 'subject_teacher';
    case AdminStaff = 'admin_staff';

    public function label(): string
    {
        return match ($this) {
            self::HomeroomTeacher => 'مربي الفصل',
            self::SubjectTeacher => 'معلم المادة',
            self::AdminStaff => 'الإداري / المشرف',
        };
    }
}
