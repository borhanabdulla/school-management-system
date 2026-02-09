<?php

namespace App\Domains\HR\Enums;

/**
 * أنواع الأدوار الوظيفية للموظفين
 */
enum StaffRole: string
{
    case Teacher = 'teacher';
    case Admin = 'admin';
    case Accountant = 'accountant';
    case Driver = 'driver';
    case Guard = 'guard';
    case HR = 'hr';
    case Other = 'other';

    /**
     * التسمية بالعربية
     */
    public function label(): string
    {
        return match ($this) {
            self::Teacher => 'معلم',
            self::Admin => 'إداري',
            self::Accountant => 'محاسب',
            self::Driver => 'سائق',
            self::Guard => 'حارس أمن',
            self::HR => 'موارد بشرية',
            self::Other => 'أخرى',
        };
    }

    /**
     * هل هذا الدور يتطلب إنشاء سجل Teacher؟
     */
    public function requiresTeacherRecord(): bool
    {
        return $this === self::Teacher;
    }

    /**
     * الحصول على قائمة الأدوار للـ Select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->all();
    }
}
