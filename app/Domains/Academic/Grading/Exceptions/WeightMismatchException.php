<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Exceptions;

/**
 * WeightMismatchException - مجموع الأوزان لا يساوي 100%
 */
class WeightMismatchException extends GradingException
{
    public function __construct(
        public readonly int $templateId,
        public readonly float $actualWeight
    ) {
        parent::__construct(
            "مجموع أوزان الفئات في القالب {$templateId} هو {$actualWeight}% بدلاً من 100%"
        );
    }
}
