<?php

namespace App\Domains\Shared\Enums;

enum DayOfWeek: int
{
    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    public function label(): string
    {
        return match ($this) {
            self::Sunday => 'الأحد',
            self::Monday => 'الاثنين',
            self::Tuesday => 'الثلاثاء',
            self::Wednesday => 'الأربعاء',
            self::Thursday => 'الخميس',
            self::Friday => 'الجمعة',
            self::Saturday => 'السبت',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Sunday => 'أحد',
            self::Monday => 'إثنين',
            self::Tuesday => 'ثلاثاء',
            self::Wednesday => 'أربعاء',
            self::Thursday => 'خميس',
            self::Friday => 'جمعة',
            self::Saturday => 'سبت',
        };
    }

    /**
     * أيام العمل المدرسية
     */
    public static function schoolDays(): array
    {
        return [
            self::Sunday,
            self::Monday,
            self::Tuesday,
            self::Wednesday,
            self::Thursday,
        ];
    }
}
