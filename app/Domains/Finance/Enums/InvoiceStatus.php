<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

/**
 * InvoiceStatus - حالات الفاتورة
 */
enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'غير مدفوعة',
            self::PartiallyPaid => 'مدفوعة جزئياً',
            self::Paid => 'مدفوعة',
            self::Cancelled => 'ملغاة',
            self::Overdue => 'متأخرة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'yellow',
            self::PartiallyPaid => 'blue',
            self::Paid => 'green',
            self::Cancelled => 'gray',
            self::Overdue => 'red',
        };
    }

    public static function unpaidValues(): array
    {
        return [
            self::Unpaid->value,
            self::PartiallyPaid->value,
            self::Overdue->value,
        ];
    }
}
