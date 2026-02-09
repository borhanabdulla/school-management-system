<?php

namespace App\Domains\HR\Payroll\Enums;

/**
 * PayrollItemType - نوع بند الراتب
 * 
 * earning = استحقاق (الراتب الأساسي، البدلات، المكافآت)
 * deduction = استقطاع (خصومات، أقساط، تأمينات)
 */
enum PayrollItemType: string
{
    case Earning = 'earning';
    case Deduction = 'deduction';

    public function label(): string
    {
        return match ($this) {
            self::Earning => 'استحقاق',
            self::Deduction => 'استقطاع',
        };
    }

    public function sign(): int
    {
        return match ($this) {
            self::Earning => 1,
            self::Deduction => -1,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Earning => 'success',
            self::Deduction => 'danger',
        };
    }
}
