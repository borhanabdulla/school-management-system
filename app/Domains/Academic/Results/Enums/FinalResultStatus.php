<?php

namespace App\Domains\Academic\Results\Enums;

enum FinalResultStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Incomplete = 'incomplete';
    case Absent = 'absent';
    case Pending = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Pass => 'ناجح',
            self::Fail => 'راسب',
            self::Incomplete => 'غير مكتمل',
            self::Absent => 'غائب',
            self::Pending => 'قيد المعالجة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pass => 'success',
            self::Fail => 'danger',
            self::Incomplete => 'warning',
            self::Absent => 'warning',
            self::Pending => 'info',
        };
    }
}
