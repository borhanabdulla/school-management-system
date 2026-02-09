<?php

namespace App\Infrastructure\Support\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * التحقق من تداخل فترتين زمنيتين
     */
    public static function datesOverlap(
        Carbon $start1, Carbon $end1,
        Carbon $start2, Carbon $end2
    ): bool {
        return $start1->lte($end2) && $end1->gte($start2);
    }
    
    /**
     * التحقق من صحة النطاق
     */
    public static function isValidRange(Carbon $start, Carbon $end): bool
    {
        return $end->gt($start);
    }
    
    /**
     * حساب الأيام المتبقية
     */
    public static function daysRemaining(Carbon $endDate): int
    {
        return now()->diffInDays($endDate, false);
    }
    
    /**
     * حساب نسبة التقدم
     */
    public static function progressPercentage(Carbon $start, Carbon $end): float
    {
        $now = now();
        
        if ($now->lt($start)) return 0;
        if ($now->gt($end)) return 100;
        
        $total = $start->diffInDays($end);
        $passed = $start->diffInDays($now);
        
        return round(($passed / $total) * 100, 2);
    }
}
