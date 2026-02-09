<?php

namespace App\Domains\Finance\Ledger\Enums;

/**
 * حالة القيد المالي
 */
enum LedgerStatus: string
{
    case Posted = 'posted';       // مثبت/سارٍ
    case Cancelled = 'cancelled'; // ملغى
}
