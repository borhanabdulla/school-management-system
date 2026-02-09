<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Data;

use App\Infrastructure\Data\BaseData;

/**
 * ClassStatsData - DTO لإحصائيات الفصل
 * 
 * يُستخدم في GradebookStatsService لتمثيل الإحصائيات المحسوبة
 */
class ClassStatsData extends BaseData
{
    public function __construct(
        public readonly float $average,
        public readonly float $max,
        public readonly float $min,
        public readonly int $passingCount,
        public readonly int $totalCount,
        public readonly float $passingRate,
    ) {
    }

    /**
     * حساب الإحصائيات من مصفوفة الدرجات
     * 
     * @param array<float> $scores
     * @param float $passThreshold
     */
    public static function fromScores(array $scores, float $passThreshold = 50): self
    {
        if (empty($scores)) {
            return new self(
                average: 0,
                max: 0,
                min: 0,
                passingCount: 0,
                totalCount: 0,
                passingRate: 0,
            );
        }

        $total = count($scores);
        $passing = count(array_filter($scores, fn($s) => $s >= $passThreshold));

        return new self(
            average: round(array_sum($scores) / $total, 2),
            max: max($scores),
            min: min($scores),
            passingCount: $passing,
            totalCount: $total,
            passingRate: round(($passing / $total) * 100, 1),
        );
    }
}
