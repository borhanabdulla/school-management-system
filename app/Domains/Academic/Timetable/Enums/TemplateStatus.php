<?php

namespace App\Domains\Academic\Timetable\Enums;

enum TemplateStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Active => 'نشط',
            self::Archived => 'مؤرشف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::Archived => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            self::Active => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            self::Archived => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
        };
    }

    /**
     * هل يمكن تعديل القالب في هذه الحالة؟
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /**
     * هل يمكن استخدام القالب في الجدول الدراسي؟
     */
    public function isUsable(): bool
    {
        return $this === self::Active;
    }
}
