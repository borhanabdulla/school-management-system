<?php

namespace App\Domains\Academic\Results\Enums;

enum FinalResultStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::Pass => 'ناجح',
            self::Fail => 'راسب',
            self::Absent => 'غائب',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pass => 'success',
            self::Fail => 'danger',
            self::Absent => 'warning',
        };
    }
}
