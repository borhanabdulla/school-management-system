<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

/**
 * PaymentMethod - طرق الدفع المدعومة
 * 
 * PR-B2: فقط النقد والتحويل اليدوي - بدون تكامل بنكي
 * 
 * القيم المسموحة:
 * - cash: نقداً
 * - manual_transfer: تحويل يدوي (إيصال بنكي يدوي)
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case ManualTransfer = 'manual_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً',
            self::ManualTransfer => 'تحويل يدوي',
        };
    }
}
