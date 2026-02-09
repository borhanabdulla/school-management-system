<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Data;

use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Domains\Shared\Enums\DayOfWeek;
use App\Infrastructure\Data\BaseData;
use App\Domains\Academic\Timetable\Models\TimeSlot;

/**
 * TimeSlotData - DTO لبيانات حصة واحدة
 */
class TimeSlotData extends BaseData
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $dayOfWeek,
        public readonly string $label,
        public readonly int $orderIndex,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly TimeSlotType $type,
        public readonly bool $isAttendanceCheckpoint = false,
    ) {
    }

    /**
     * إنشاء من مصفوفة (Override BaseData)
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'] ?? null,
            dayOfWeek: (int) ($data['day_of_week'] ?? 0),
            label: $data['label'] ?? '',
            orderIndex: (int) ($data['order_index'] ?? 0),
            startTime: $data['start_time'] ?? '00:00',
            endTime: $data['end_time'] ?? '00:00',
            type: TimeSlotType::tryFrom($data['type'] ?? '') ?? TimeSlotType::Academic,
            isAttendanceCheckpoint: (bool) ($data['is_attendance_checkpoint'] ?? false),
        );
    }

    /**
     * إنشاء من Model
     */
    public static function fromModel($model): static
    {
        /** @var TimeSlot $slot */
        $slot = $model;
        return new self(
            id: $slot->id,
            dayOfWeek: $slot->day_of_week,
            label: $slot->label,
            orderIndex: $slot->order_index,
            startTime: $slot->start_time?->format('H:i') ?? '00:00',
            endTime: $slot->end_time?->format('H:i') ?? '00:00',
            type: $slot->type ?? TimeSlotType::Academic,
            isAttendanceCheckpoint: $slot->is_attendance_checkpoint ?? false,
        );
    }

    /**
     * تحويل إلى مصفوفة للحفظ في الموديل
     */
    public function toModelArray(): array
    {
        return [
            'day_of_week' => $this->dayOfWeek,
            'label' => $this->label,
            'order_index' => $this->orderIndex,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'type' => $this->type->value,
            'is_attendance_checkpoint' => $this->isAttendanceCheckpoint,
        ];
    }

    /**
     * حساب المدة بالدقائق
     */
    public function getDurationMinutes(): int
    {
        $start = strtotime($this->startTime);
        $end = strtotime($this->endTime);
        return ($end - $start) / 60;
    }

    /**
     * اسم اليوم
     */
    public function getDayName(): string
    {
        return DayOfWeek::tryFrom($this->dayOfWeek)?->label() ?? '';
    }
}
