<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Exceptions;

use Exception;

/**
 * يُطرح عند وجود خطأ في إعدادات الحصص
 */
class InvalidTimeSlotsException extends Exception
{
    protected array $errors;

    public function __construct(array $errors, ?string $message = null)
    {
        $this->errors = $errors;

        if (!$message) {
            $message = "توجد أخطاء في إعدادات الحصص: " . implode('، ', $errors);
        }

        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function overlappingSlots(int $dayOfWeek, string $slot1, string $slot2): self
    {
        return new self(
            ["تداخل في اليوم {$dayOfWeek}: {$slot1} يتقاطع مع {$slot2}"],
            "يوجد تداخل في أوقات الحصص"
        );
    }

    public static function invalidTimeRange(string $start, string $end): self
    {
        return new self(
            ["وقت البداية ({$start}) يجب أن يكون قبل وقت النهاية ({$end})"],
            "نطاق زمني غير صالح"
        );
    }

    public static function noSlotsProvided(): self
    {
        return new self(
            ["لا توجد حصص معرفة"],
            "يجب تعريف حصة واحدة على الأقل"
        );
    }
}
