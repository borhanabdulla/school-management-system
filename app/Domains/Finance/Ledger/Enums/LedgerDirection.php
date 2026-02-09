<?php

namespace App\Domains\Finance\Ledger\Enums;

/**
 * اتجاه الحركة المالية
 */
enum LedgerDirection: string
{
    case In = 'in';   // دخل (من الطلاب، إلخ)
    case Out = 'out'; // خرج (رواتب، مصاريف، إلخ)
}
