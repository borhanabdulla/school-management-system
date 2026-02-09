<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Exceptions;

use Exception;
use App\Domains\Academic\Timetable\Models\TimeSlot;

/**
 * يُطرح عند محاولة حذف حصص زمنية مستخدمة في جداول دراسية
 * 
 * This exception is thrown when attempting to delete timeSlots that are
 * referenced by timetable entries, preventing data orphaning.
 */
class CannotDeleteTimeSlotsInUseException extends Exception
{
    protected array $timeSlotIds;
    protected int $usageCount;

    /**
     * @param array $timeSlotIds Array of time slot IDs that are in use
     * @param int $usageCount Number of timetable entries referencing these slots
     * @param string|null $customMessage
     */
    public function __construct(
        array $timeSlotIds,
        int $usageCount,
        ?string $customMessage = null
    ) {
        $this->timeSlotIds = $timeSlotIds;
        $this->usageCount = $usageCount;

        if (!$customMessage) {
            $count = count($timeSlotIds);
            $countText = $count <= 5 ? implode('، ', $timeSlotIds) : implode('، ', array_slice($timeSlotIds, 0, 5)) . '...';
            $customMessage = "لا يمكن حذف الحصص الزمنية ({$countText}) لأنها مستخدمة في {$usageCount} حصة/حصص في الجدول الدراسي. يرجى حذف الحصص المرتبطة أولاً أو استخدام قالب جديد.";
        }

        parent::__construct($customMessage);
    }

    /**
     * Get the time slot IDs that are in use
     */
    public function getTimeSlotIds(): array
    {
        return $this->timeSlotIds;
    }

    /**
     * Get the number of timetable entries using these slots
     */
    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    /**
     * Get the count of affected time slots
     */
    public function getAffectedTimeSlotCount(): int
    {
        return count($this->timeSlotIds);
    }

    /**
     * Create exception for single time slot
     */
    public static function forSingleSlot(TimeSlot $timeSlot, int $usageCount): self
    {
        return new self(
            [$timeSlot->id],
            $usageCount,
            "لا يمكن حذف الحصة الزمنية \"{$timeSlot->label}\" لأنها مستخدمة في {$usageCount} حصة/حصص في الجدول الدراسي."
        );
    }

    /**
     * Create exception for multiple time slots
     */
    public static function forMultipleSlots(array $timeSlots, int $usageCount): self
    {
        $ids = array_map(fn(TimeSlot $slot) => $slot->id, $timeSlots);
        $labels = array_map(fn(TimeSlot $slot) => $slot->label, $timeSlots);
        
        $count = count($timeSlots);
        $labelPreview = implode('، ', $labels);
        $extraLabel = $count > 3 ? " ({$count} حصص)" : "";

        return new self(
            $ids,
            $usageCount,
            "لا يمكن حذف الحصص الزمنية: {$labelPreview}{$extraLabel} لأنها مستخدمة في {$usageCount} حصة/حصص في الجدول الدراسي."
        );
    }
}
