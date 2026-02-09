<?php

namespace App\Domains\HR\Payroll\Enums;

enum PayoutMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً',
            self::BankTransfer => 'تحويل بنكي',
            self::Cheque => 'شيك',
        };
    }
}
