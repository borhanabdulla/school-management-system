<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Data;

use App\Infrastructure\Data\BaseData;

/**
 * GradingTemplateData - كائن بيانات قالب الدرجات
 * 
 * يُستخدم لإنشاء أو تحديث قوالب الدرجات
 */
class GradingTemplateData extends BaseData
{
    public function __construct(
        public readonly string $name,
        public readonly float $totalMaxScore = 100,
        public readonly float $passScore = 50,
        public readonly string $roundingRule = 'nearest_integer',
        public readonly int $roundingPrecision = 0,
        public readonly ?int $academicYearId = null,
        public readonly ?int $gradeId = null,
        public readonly ?int $termId = null,
    ) {
    }
}
