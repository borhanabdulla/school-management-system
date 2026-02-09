<?php

namespace App\Domains\Academic\AcademicYear\Enums;

enum AcademicYearStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'مسودة (قيد الإعداد)',
            self::Active => 'نشطة حالياً',
            self::Closed => 'مغلقة',
            self::Archived => 'مؤرشفة',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Active => 'green',
            self::Closed => 'orange',
            self::Archived => 'gray',
        };
    }

    // هذه هي الدالة التي كانت ناقصة وسببت الخطأ
    public function styles(): string
    {
        $color = $this->color();
        
        // تقوم بإرجاع تنسيقات Tailwind بناءً على اللون
        return "bg-{$color}-100 text-{$color}-800 border-{$color}-200 dark:bg-{$color}-900/30 dark:text-{$color}-300 dark:border-{$color}-700 border";
    }

    public function canBeEdited(): bool
    {
        return $this === self::Pending;
    }

    public function canBeDeleted(): bool
    {
        return $this === self::Pending;
    }
}