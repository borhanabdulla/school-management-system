<?php

namespace App\Domains\Academic\Term\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عند محاولة تفعيل فصل دراسي لسنة أكاديمية غير نشطة
 */
class TermYearNotActiveException extends InvalidOperationException
{
    public function __construct(int $termId, int $yearId)
    {
        parent::__construct(
            'لا يمكن تفعيل فصل دراسي لسنة أكاديمية غير نشطة.',
            'activate_term',
            'year_not_active'
        );
    }
}
