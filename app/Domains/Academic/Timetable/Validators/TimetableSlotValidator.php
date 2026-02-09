<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Validators;

use App\Domains\Academic\Timetable\Exceptions\InvalidTimeSlotsException;

/**
 * TimetableSlotValidator - التحقق من صحة الحصص
 */
class TimetableSlotValidator
{
    /**
     * التحقق من عدم تداخل الحصص
     * 
     * @throws InvalidTimeSlotsException
     */
    public function validateSlots(array $slots): void
    {
        $errors = $this->getSlotErrors($slots);

        if (!empty($errors)) {
            throw new InvalidTimeSlotsException($errors);
        }
    }

    /**
     * جلب أخطاء الحصص (بدون رمي Exception)
     */
    public function getSlotErrors(array $slots): array
    {
        $errors = [];
        $slotsByDay = collect($slots)->groupBy(fn($s) => $s['day_of_week'] ?? $s->dayOfWeek ?? 0);

        foreach ($slotsByDay as $day => $daySlots) {
            $sorted = $daySlots->sortBy(fn($s) => $s['order_index'] ?? $s->orderIndex ?? 0)->values();

            for ($i = 0; $i < count($sorted) - 1; $i++) {
                $current = $sorted[$i];
                $next = $sorted[$i + 1];

                $currentEnd = strtotime($current['end_time'] ?? $current->endTime ?? '');
                $nextStart = strtotime($next['start_time'] ?? $next->startTime ?? '');

                if ($currentEnd > $nextStart) {
                    $errors[] = "تداخل في اليوم {$day}: الحصة #{$i} تنتهي بعد بدء الحصة #" . ($i + 1);
                }
            }
        }

        return $errors;
    }
}
