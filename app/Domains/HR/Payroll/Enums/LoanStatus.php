<?php

namespace App\Domains\HR\Payroll\Enums;

enum LoanStatus: string
{

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الانتظار',
            self::Approved => 'مقبول',
            self::Rejected => 'مرفوض',
            self::Paid => 'مدفوع بالكامل',
            self::Cancelled => 'ملغي',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::Paid => 'success',
            self::Cancelled => 'gray',
        };
    }
}
