<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Data;

use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Shared\Enums\DayOfWeek;
use App\Infrastructure\Data\BaseData;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;

/**
 * TimetableTemplateData - DTO لبيانات قالب الدوام
 */
class TimetableTemplateData extends BaseData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $workingDays,
        public readonly bool $isDefault,
        public readonly TemplateStatus $status,
        public readonly int $academicYearId,
        public readonly ?int $educationalStageId,
        public readonly array $slots = [],
        public readonly array $gradeIds = [],
    ) {
    }

    /**
     * إنشاء من Livewire Form
     */
    public static function fromLivewireForm(
        array $formData,
        array $slotsData,
        array $gradeIds
    ): self {
        $slots = collect($slotsData)->map(fn($s) => TimeSlotData::fromArray($s))->toArray();

        return new self(
            id: $formData['id'] ?? null,
            name: $formData['name'],
            description: $formData['description'] ?? null,
            workingDays: $formData['working_days'] ?? DayOfWeek::schoolDays(),
            isDefault: $formData['is_default'] ?? false,
            status: TemplateStatus::tryFrom($formData['status'] ?? 'draft') ?? TemplateStatus::Draft,
            academicYearId: $formData['academic_year_id'],
            educationalStageId: $formData['educational_stage_id'] ?? null,
            slots: $slots,
            gradeIds: $gradeIds,
        );
    }

    /**
     * إنشاء من Model موجود
     */
    public static function fromModel($model): static
    {
        /** @var TimetableTemplate $template */
        $template = $model;
        $slots = $template->timeSlots->map(fn($s) => TimeSlotData::fromModel($s))->toArray();
        $gradeIds = $template->grades->pluck('id')->toArray();

        return new self(
            id: $template->id,
            name: $template->name,
            description: $template->description,
            workingDays: $template->working_days ?? [],
            isDefault: $template->is_default,
            status: $template->status,
            academicYearId: $template->academic_year_id,
            educationalStageId: $template->educational_stage_id,
            slots: $slots,
            gradeIds: $gradeIds,
        );
    }

    /**
     * تحويل إلى مصفوفة للحفظ في الموديل
     */
    public function toModelArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'working_days' => $this->workingDays,
            'is_default' => $this->isDefault,
            'status' => $this->status->value,
            'academic_year_id' => $this->academicYearId,
            'educational_stage_id' => $this->educationalStageId,
        ];
    }
}
