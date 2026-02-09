<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Exceptions;

/**
 * SyncFailedException - فشل عملية المزامنة
 */
class SyncFailedException extends GradingException
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $categoryId,
        string $reason
    ) {
        parent::__construct(
            "فشل مزامنة الدرجة للطالب {$studentId} في الفئة {$categoryId}: {$reason}"
        );
    }
}
