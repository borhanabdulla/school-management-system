<?php

namespace App\Domains\HR\Payroll\Enums;

enum PayrollBatchStatus: string
{

    case Draft = 'draft';
    case Frozen = 'frozen';
    case Approved = 'approved';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Frozen => 'مجمد (قيد المراجعة)',
            self::Approved => 'معتمد',
            self::Paid => 'مصروف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Frozen => 'info',
            self::Approved => 'success',
            self::Paid => 'success', // or emerald if supported
        };
    }
}
