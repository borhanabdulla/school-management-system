<?php

namespace App\Domains\Academic\Student\Enums;

enum GuardianRelationship: string
{
    case Father = 'father';
    case Mother = 'mother';
    case Grandfather = 'grandfather';
    case Grandmother = 'grandmother';
    case Uncle = 'uncle';
    case Aunt = 'aunt';
    case Brother = 'brother';
    case Sister = 'sister';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Father => 'أب',
            self::Mother => 'أم',
            self::Grandfather => 'جد',
            self::Grandmother => 'جدة',
            self::Uncle => 'عم/خال',
            self::Aunt => 'عمة/خالة',
            self::Brother => 'أخ',
            self::Sister => 'أخت',
            self::Other => 'آخر',
        };
    }
}
