<?php

namespace App\Domains\Academic\Term\Enums;

enum TermStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الانتظار',
            self::Active => 'نشط',
            self::Completed => 'مكتمل',
        };
    }
}
