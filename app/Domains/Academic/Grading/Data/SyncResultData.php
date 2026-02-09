<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Data;

use App\Infrastructure\Data\BaseData;

/**
 * SyncResultData - DTO لنتيجة المزامنة
 * 
 * يُرجع من GradeSyncService لتوضيح نتيجة عملية المزامنة
 */
class SyncResultData extends BaseData
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $categoryId,
        public readonly float $rawScore,
        public readonly float $normalizedScore,
        public readonly string $source,      // 'monthly', 'attendance', 'homework'
        public readonly bool $success,
        public readonly ?string $error = null,
    ) {
    }

    /**
     * إنشاء نتيجة ناجحة
     */
    public static function success(
        int $studentId,
        int $categoryId,
        float $rawScore,
        float $normalizedScore,
        string $source
    ): self {
        return new self(
            studentId: $studentId,
            categoryId: $categoryId,
            rawScore: $rawScore,
            normalizedScore: $normalizedScore,
            source: $source,
            success: true,
        );
    }

    /**
     * إنشاء نتيجة فاشلة
     */
    public static function failed(
        int $studentId,
        int $categoryId,
        string $error,
        string $source = 'unknown'
    ): self {
        return new self(
            studentId: $studentId,
            categoryId: $categoryId,
            rawScore: 0,
            normalizedScore: 0,
            source: $source,
            success: false,
            error: $error,
        );
    }
    /**
     * إنشاء نتيجة مجمعة (Aggregated)
     */
    public static function aggregated(
        int $studentId,
        int $categoryId,
        float $aggregatedScore,
        string $source
    ): self {
        return new self(
            studentId: $studentId,
            categoryId: $categoryId,
            rawScore: $aggregatedScore, // نستخدم القيمة المجمعة كدرجة خام
            normalizedScore: 0, // سيتم حسابها لاحقاً أو تمريرها إذا لزم الأمر
            source: $source,
            success: true,
            error: null,
        );
    }

    /**
     * إنشاء نتيجة مؤجلة (Deferred)
     */
    public static function deferred(
        int $studentId,
        int $categoryId,
        string $message,
        string $source
    ): self {
        return new self(
            studentId: $studentId,
            categoryId: $categoryId,
            rawScore: 0,
            normalizedScore: 0,
            source: $source,
            success: true,
            error: $message, // نستخدم حقل الخطأ لتمرير الرسالة مؤقتاً أو نضيف حقل message
        );
    }
}
