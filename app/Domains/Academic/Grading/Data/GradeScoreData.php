<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Data;

use App\Infrastructure\Data\BaseData;

/**
 * GradeScoreData - DTO للدرجة (AGS Style)
 * 
 * يحمل الدرجة الخام والمُعايرة معاً للشفافية:
 * - raw_score: الدرجة كما أدخلها المعلم
 * - normalized_score: الدرجة بعد تطبيق الوزن النسبي
 * 
 * المعادلة: normalized = (raw / max) × weight
 */
class GradeScoreData extends BaseData
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $categoryId,
        public readonly float $rawScore,
        public readonly float $maxScore,
        public readonly float $categoryWeight,
        public readonly float $normalizedScore,
    ) {
    }

    /**
     * حساب الدرجة المُعايرة وإنشاء DTO
     */
    public static function calculate(
        int $studentId,
        int $categoryId,
        float $rawScore,
        float $maxScore,
        float $categoryWeight
    ): self {
        // حماية القسمة على صفر
        $normalized = $maxScore > 0
            ? ($rawScore / $maxScore) * $categoryWeight
            : 0;

        return new self(
            studentId: $studentId,
            categoryId: $categoryId,
            rawScore: $rawScore,
            maxScore: $maxScore,
            categoryWeight: $categoryWeight,
            normalizedScore: round($normalized, 2),
        );
    }

    /**
     * النسبة المئوية للدرجة الخام
     */
    public function getPercentage(): float
    {
        return $this->maxScore > 0
            ? round(($this->rawScore / $this->maxScore) * 100, 1)
            : 0;
    }
}
