<?php

namespace App\Domains\Academic\ClassSection\Enums;

enum SectionGenderType: string
{
    case Boys = 'boys';
    case Girls = 'girls';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Boys => 'بنين',
            self::Girls => 'بنات',
            self::Mixed => 'مختلط',
        };
    }
}
