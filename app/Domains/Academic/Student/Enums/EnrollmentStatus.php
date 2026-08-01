<?php

namespace App\Domains\Academic\Student\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Failed = 'failed';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Completed => 'مكتمل',
            self::Failed => 'راسب',
            self::Withdrawn => 'منسحب',
        };
    }
}
