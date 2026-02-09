<?php

namespace App\Livewire\Forms\Calendar;

use Livewire\Form;
use Livewire\Attributes\Validate;

/**
 * Form Object لإنشاء/تعديل أحداث المدرسة
 */
class EventForm extends Form
{
    // ============================================
    // بيانات الحدث الأساسية
    // ============================================
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:2000')]
    public ?string $description = null;

    #[Validate('required|in:holiday,exam,meeting,activity,other')]
    public string $type = 'activity';

    #[Validate('required|date')]
    public string $startDate = '';

    #[Validate('nullable|date|after_or_equal:startDate')]
    public ?string $endDate = null;

    #[Validate('boolean')]
    public bool $isAllDay = true;

    #[Validate('nullable|date_format:H:i')]
    public ?string $startTime = null;

    #[Validate('nullable|date_format:H:i|after:startTime')]
    public ?string $endTime = null;

    #[Validate('nullable|string|max:255')]
    public ?string $location = null;

    #[Validate('nullable|string|max:50')]
    public ?string $color = '#3b82f6';

    #[Validate('boolean')]
    public bool $isPublic = true;

    // ============================================
    // الجمهور المستهدف
    // ============================================
    #[Validate('array')]
    public array $targetAudience = ['all']; // all, students, teachers, parents

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:holiday,exam,meeting,activity,other',
            'startDate' => 'required|date',
            'isAllDay' => 'boolean',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'isPublic' => 'boolean',
            'targetAudience' => 'array',
        ];

        if (!$this->isAllDay) {
            $rules['startTime'] = 'required|date_format:H:i';
            $rules['endTime'] = 'nullable|date_format:H:i';
        }

        if ($this->endDate) {
            $rules['endDate'] = 'date|after_or_equal:startDate';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الحدث مطلوب',
            'startDate.required' => 'تاريخ البداية مطلوب',
            'startTime.required' => 'وقت البداية مطلوب للأحداث غير اليومية',
            'endDate.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
        ];
    }

    /**
     * التحقق إذا كان الحدث يمتد لأيام متعددة
     */
    public function isMultiDay(): bool
    {
        return $this->endDate && $this->endDate !== $this->startDate;
    }

    public function setFromModel(\App\Domains\Academic\Calendar\Models\SchoolEvent $event): void
    {
        $this->title = $event->title;
        $this->description = $event->description;
        $this->type = $event->type;
        $this->startDate = $event->start_date->format('Y-m-d');
        $this->endDate = $event->end_date?->format('Y-m-d');
        $this->isAllDay = $event->is_all_day;
        $this->startTime = $event->start_time;
        $this->endTime = $event->end_time;
        $this->location = $event->location;
        $this->color = $event->color ?? '#3b82f6';
        $this->isPublic = $event->is_public;
        $this->targetAudience = $event->target_audience ?? ['all'];
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate ?: $this->startDate,
            'is_all_day' => $this->isAllDay,
            'start_time' => $this->isAllDay ? null : $this->startTime,
            'end_time' => $this->isAllDay ? null : $this->endTime,
            'location' => $this->location,
            'color' => $this->color,
            'is_public' => $this->isPublic,
            'target_audience' => $this->targetAudience,
        ];
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->title = '';
        $this->description = null;
        $this->type = 'activity';
        $this->startDate = '';
        $this->endDate = null;
        $this->isAllDay = true;
        $this->startTime = null;
        $this->endTime = null;
        $this->location = null;
        $this->color = '#3b82f6';
        $this->isPublic = true;
        $this->targetAudience = ['all'];
    }
}
