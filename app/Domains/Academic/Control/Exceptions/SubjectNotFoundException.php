<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا تُوجد المادة
 */
class SubjectNotFoundException extends InvalidOperationException
{
    public function __construct(int $courseOfferingId)
    {
        parent::__construct(
            'المادة غير موجودة.',
            'submit_grade',
            'subject_not_found'
        );
    }
}
