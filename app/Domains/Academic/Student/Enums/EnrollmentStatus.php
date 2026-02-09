<?php

namespace App\Domains\Academic\Student\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Failed = 'failed';
    case Withdrawn = 'withdrawn';
    case Returning = 'returning';
    case New = 'new';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Completed => 'مكتمل',
            self::Failed => 'راسب',
            self::Withdrawn => 'منسحب',
            self::Returning => 'معيد',
            self::New => 'مستجد',
        };
    }
}
