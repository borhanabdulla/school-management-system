<?php

namespace App\Domains\HR\Staff\Enums;

enum StaffStatus: string
{

    case Active = 'active';
    case Terminated = 'terminated';
    case Resigned = 'resigned';
    case OnLeave = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Terminated => 'منهي خدماته',
            self::Resigned => 'مستقيل',
            self::OnLeave => 'في إجازة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Terminated => 'danger',
            self::Resigned => 'warning',
            self::OnLeave => 'info',
        };
    }
}
