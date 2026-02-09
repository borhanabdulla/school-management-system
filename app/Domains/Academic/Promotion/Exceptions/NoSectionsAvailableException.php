<?php

namespace App\Domains\Academic\Promotion\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا توجد شعب للصف في السنة الجديدة
 */
class NoSectionsAvailableException extends InvalidOperationException
{
    public function __construct(int $gradeId, int $yearId)
    {
        parent::__construct(
            'لا توجد شعب لهذا الصف في السنة الجديدة',
            'auto_distribute',
            'no_sections_available'
        );
    }
}
