<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Data;

use App\Infrastructure\Data\BaseData;

/**
 * GradeResultData - كائن نتيجة الدرجة
 * 
 * يمثل نتيجة حساب درجة طالب لمادة أو فئة معينة
 */
class GradeResultData extends BaseData
{
    public function __construct(
        public readonly float $total,
        public readonly float $max,
        public readonly float $percentage,
        public readonly bool $passed,
        public readonly ?string $letter = null,
        public readonly array $categories = [],
    ) {
    }
}
