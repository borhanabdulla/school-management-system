<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما تكون الدرجة غير صالحة (سالبة أو تتجاوز الحد الأقصى)
 */
class InvalidScoreException extends InvalidOperationException
{
    public static function exceedsMax(float $score, float $maxScore): self
    {
        return new self(
            "الدرجة ({$score}) تتجاوز الدرجة العظمى للمادة ({$maxScore}).",
            'submit_grade',
            'score_exceeds_max'
        );
    }

    public static function negative(): self
    {
        return new self(
            'الدرجة لا يمكن أن تكون سالبة.',
            'submit_grade',
            'negative_score'
        );
    }
}
