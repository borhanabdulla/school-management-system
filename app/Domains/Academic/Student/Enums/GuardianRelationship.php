<?php

namespace App\Domains\Academic\Student\Enums;

enum GuardianRelationship: string
{
    case Father = 'father';
    case Mother = 'mother';
    case Uncle = 'uncle';
    case Brother = 'brother';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Father => 'أب',
            self::Mother => 'أم',
            self::Uncle => 'عم/خال',
            self::Brother => 'أخ',
            self::Other => 'آخر',
        };
    }
}
