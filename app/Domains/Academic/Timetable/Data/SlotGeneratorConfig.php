<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Data;

use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Infrastructure\Data\BaseData;

/**
 * SlotGeneratorConfig - DTO لإعدادات المولد الذكي
 */
class SlotGeneratorConfig extends BaseData
{
    public function __construct(
        public readonly string $dayStartTime = '07:30',
        public readonly int $slotDuration = 45,
        public readonly int $numberOfSlots = 7,
        public readonly array $breaks = [],
        public readonly bool $includeAssembly = false,
        public readonly int $assemblyDuration = 10,
    ) {
    }

    /**
     * توليد الحصص ليوم واحد
     * 
     * @return array<TimeSlotData>
     */
    public function generateSlotsForDay(int $dayOfWeek): array
    {
        $slots = [];
        $currentTime = strtotime($this->dayStartTime);
        $orderIndex = 0;

        // الطابور الصباحي
        if ($this->includeAssembly && $this->assemblyDuration > 0) {
            $endTime = $currentTime + ($this->assemblyDuration * 60);
            $slots[] = new TimeSlotData(
                id: null,
                dayOfWeek: $dayOfWeek,
                label: 'الطابور الصباحي',
                orderIndex: $orderIndex++,
                startTime: date('H:i', $currentTime),
                endTime: date('H:i', $endTime),
                type: TimeSlotType::Assembly,
            );
            $currentTime = $endTime;
        }

        // الحصص والاستراحات
        $academicSlotNumber = 1;
        $breakNumber = 1;

        for ($i = 0; $i < $this->numberOfSlots; $i++) {
            $endTime = $currentTime + ($this->slotDuration * 60);
            $slots[] = new TimeSlotData(
                id: null,
                dayOfWeek: $dayOfWeek,
                label: "الحصة " . $this->getArabicOrdinal($academicSlotNumber),
                orderIndex: $orderIndex++,
                startTime: date('H:i', $currentTime),
                endTime: date('H:i', $endTime),
                type: TimeSlotType::Academic,
            );
            $currentTime = $endTime;
            $academicSlotNumber++;

            // فحص الاستراحات
            foreach ($this->breaks as $break) {
                if (($break['after_slot'] ?? 0) === $academicSlotNumber - 1) {
                    $breakDuration = (int) ($break['duration'] ?? 15);
                    $breakEnd = $currentTime + ($breakDuration * 60);

                    $slots[] = new TimeSlotData(
                        id: null,
                        dayOfWeek: $dayOfWeek,
                        label: count($this->breaks) > 1
                        ? "الفسحة " . $this->getArabicOrdinal($breakNumber)
                        : "الفسحة",
                        orderIndex: $orderIndex++,
                        startTime: date('H:i', $currentTime),
                        endTime: date('H:i', $breakEnd),
                        type: TimeSlotType::Break ,
                    );
                    $currentTime = $breakEnd;
                    $breakNumber++;
                }
            }
        }

        return $slots;
    }

    /**
     * توليد الحصص لجميع الأيام
     */
    public function generateSlotsForAllDays(array $workingDays): array
    {
        $allSlots = [];

        foreach ($workingDays as $day) {
            $daySlots = $this->generateSlotsForDay((int) $day);
            $allSlots = array_merge($allSlots, $daySlots);
        }

        return $allSlots;
    }

    private function getArabicOrdinal(int $number): string
    {
        $ordinals = [
            1 => 'الأولى',
            2 => 'الثانية',
            3 => 'الثالثة',
            4 => 'الرابعة',
            5 => 'الخامسة',
            6 => 'السادسة',
            7 => 'السابعة',
            8 => 'الثامنة',
            9 => 'التاسعة',
            10 => 'العاشرة',
        ];

        return $ordinals[$number] ?? (string) $number;
    }
}
