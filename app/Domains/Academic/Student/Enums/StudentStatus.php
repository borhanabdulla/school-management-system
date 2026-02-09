<?php

namespace App\Domains\Academic\Student\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Graduated = 'graduated';
    case Transferred = 'transferred';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Inactive => 'غير نشط',
            self::Graduated => 'متخرج',
            self::Transferred => 'منقول',
            self::Suspended => 'موقوف',
        };
    }
}
