<?php

namespace App\Domains\Academic\Promotion\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عند محاولة التراجع عن ترحيل تم التراجع عنه مسبقاً
 */
class PromotionAlreadyRevertedException extends InvalidOperationException
{
    public function __construct()
    {
        parent::__construct(
            'تم التراجع عن هذا الترحيل مسبقاً',
            'promotion',
            'already_reverted'
        );
    }
}
