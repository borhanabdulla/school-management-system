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
        $slotsByDay = collect($slots)->groupBy(function ($s) {
            if (is_array($s)) {
                return $s['day_of_week'] ?? 0;
            }
            return $s->dayOfWeek ?? 0;
        });

        foreach ($slotsByDay as $day => $daySlots) {
            $sorted = $daySlots->sortBy(function ($s) {
                if (is_array($s)) {
                    return $s['order_index'] ?? 0;
                }
                return $s->orderIndex ?? 0;
            })->values();

            for ($i = 0; $i < count($sorted) - 1; $i++) {
                $current = $sorted[$i];
                $next = $sorted[$i + 1];

                $currentEndStr = is_array($current) ? ($current['end_time'] ?? '') : ($current->endTime ?? '');
                $nextStartStr = is_array($next) ? ($next['start_time'] ?? '') : ($next->startTime ?? '');

                $currentEnd = strtotime($currentEndStr);
                $nextStart = strtotime($nextStartStr);

                if ($currentEnd > $nextStart) {
                    $errors[] = "تداخل في اليوم {$day}: الحصة #{$i} تنتهي بعد بدء الحصة #" . ($i + 1);
                }
            }
        }

        return $errors;
    }
}
