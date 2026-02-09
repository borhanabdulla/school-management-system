<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما تكون المادة خارج ترم الجلسة الحالية
 */
class TermMismatchException extends InvalidOperationException
{
    public function __construct(int $courseOfferingId, int $sessionTermId, int $offeringTermId)
    {
        parent::__construct(
            'المادة لا تتبع نفس الترم الخاص بجلسة الكنترول.',
            'submit_grade',
            'term_mismatch'
        );
    }
}
