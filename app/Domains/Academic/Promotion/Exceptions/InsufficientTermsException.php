<?php

namespace App\Domains\Academic\Promotion\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا تحتوي السنة على عدد كافٍ من الفصول الدراسية
 */
class InsufficientTermsException extends InvalidOperationException
{
    public function __construct(int $actualCount, int $requiredCount = 2)
    {
        parent::__construct(
            "يجب أن تحتوي السنة على {$requiredCount} ترم على الأقل. الموجود: {$actualCount}",
            'aggregate_results',
            'insufficient_terms'
        );
    }
}
