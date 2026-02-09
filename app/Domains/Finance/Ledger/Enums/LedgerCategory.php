<?php

namespace App\Domains\Finance\Ledger\Enums;

/**
 * تصنيف الحركة المالية
 */
enum LedgerCategory: string
{
    case StudentPayment = 'student_payment'; // دفعة طالب
    case PayrollPayout = 'payroll_payout';   // صرف رواتب
    case Expense = 'expense';                 // مصروف عام
    case Adjustment = 'adjustment';           // تعديل/تسوية
}
