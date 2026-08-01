<?php

namespace App\Domains\Academic\Student\Enums;

enum EnrollmentType: string
{
    case New = 'new';
    case Returning = 'returning';
    case TransferIn = 'transfer_in';

    public function label(): string
    {
        return match ($this) {
            self::New => 'مستجد',
            self::Returning => 'معيد',
            self::TransferIn => 'منقول',
        };
    }
}
