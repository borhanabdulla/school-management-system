<?php

namespace App\Domains\Finance\Enums;

enum PaymentStatus: string
{
    case Posted = 'posted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Posted => 'مرحلة',
            self::Cancelled => 'ملغاة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Posted => 'success',
            self::Cancelled => 'danger',
        };
    }
}
