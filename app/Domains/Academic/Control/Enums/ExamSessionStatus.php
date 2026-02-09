<?php

namespace App\Domains\Academic\Control\Enums;

enum ExamSessionStatus: string
{

    case Setup = 'setup';
    case Active = 'active';
    case Processing = 'processing';
    case Published = 'published';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Setup => 'إعداد',
            self::Active => 'نشطة',
            self::Processing => 'قيد المعالجة',
            self::Published => 'منشورة',
            self::Closed => 'مغلقة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Setup => 'gray',
            self::Active => 'success',
            self::Processing => 'warning',
            self::Published => 'info',
            self::Closed => 'danger',
        };
    }
}
