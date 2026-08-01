<?php

namespace App\Domains\Academic\Results\Enums;

enum ResultDecision: string
{

    case Pass = 'pass';
    case Fail = 'fail';
    case Conditional = 'conditional';
    case Pending = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Pass => 'ناجح',
            self::Fail => 'راسب',
            self::Conditional => 'مشروط (دور ثاني)',
            self::Pending => 'قيد المعالجة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pass => 'success',
            self::Fail => 'danger',
            self::Conditional => 'warning',
            self::Pending => 'info',
        };
    }
}
