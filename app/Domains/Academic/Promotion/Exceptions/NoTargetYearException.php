<?php

namespace App\Domains\Academic\Promotion\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا توجد سنة دراسية جديدة للترحيل
 */
class NoTargetYearException extends InvalidOperationException
{
    public function __construct()
    {
        parent::__construct(
            'لا توجد سنة دراسية جديدة. يجب إنشاء سنة جديدة قبل الترحيل',
            'promotion',
            'no_target_year'
        );
    }
}
