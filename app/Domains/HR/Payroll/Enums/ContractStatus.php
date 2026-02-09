<?php

namespace App\Domains\HR\Payroll\Enums;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Terminated = 'terminated';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Active => 'نشط',
            self::Terminated => 'منهي',
            self::Expired => 'منتهي',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'success',
            self::Terminated => 'danger',
            self::Expired => 'warning',
        };
    }
}
