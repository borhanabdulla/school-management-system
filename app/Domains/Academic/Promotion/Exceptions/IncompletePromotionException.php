<?php

namespace App\Domains\Academic\Promotion\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لم يتم ترحيل جميع الطلاب قبل إغلاق السنة
 */
class IncompletePromotionException extends InvalidOperationException
{
    public function __construct(int $promotedCount, int $totalCount)
    {
        parent::__construct(
            "لم يتم ترحيل جميع الطلاب. تم ترحيل {$promotedCount} من {$totalCount}",
            'close_year',
            'incomplete_promotion'
        );
    }
}
