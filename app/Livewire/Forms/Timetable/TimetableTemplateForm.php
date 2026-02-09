<?php

namespace App\Livewire\Forms\Timetable;

use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Data\TimetableTemplateData;
use App\Domains\Academic\Timetable\Data\SlotGeneratorConfig;
use Livewire\Form;

class TimetableTemplateForm extends Form
{
    // البيانات الأساسية
    public ?int $id = null;
    public string $name = '';
    public ?string $description = null;
    public array $working_days = [];
    public bool $is_default = false;
    public string $status = 'draft';
    public ?int $academic_year_id = null;
    public ?int $educational_stage_id = null;

    // الصفوف المعينة
    public array $grade_ids = [];

    // الحصص (Flattened - لكل يوم)
    public array $slots = [];

    // إعدادات المولد الذكي
    public string $generator_start_time = '07:30';
    public int $generator_slot_duration = 45;
    public int $generator_number_of_slots = 7;
    public array $generator_breaks = [];
    public bool $generator_include_assembly = false;
    public int $generator_assembly_duration = 10;

    /**
     * قواعد التحقق
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'required|integer|between:0,6',
            'is_default' => 'boolean',
            'status' => 'required|in:draft,active,archived',
            'academic_year_id' => 'required|exists:academic_years,id',
            'educational_stage_id' => 'nullable|exists:educational_stages,id',
            'grade_ids' => 'array',
            'grade_ids.*' => 'exists:grades,id',
        ];
    }

    /**
     * رسائل التحقق
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم القالب مطلوب',
            'working_days.required' => 'يجب اختيار يوم عمل واحد على الأقل',
            'working_days.min' => 'يجب اختيار يوم عمل واحد على الأقل',
            'academic_year_id.required' => 'السنة الدراسية مطلوبة',
        ];
    }

    /**
     * تهيئة من موديل موجود
     */
    public function setModel(TimetableTemplate $template): void
    {
        $this->id = $template->id;
        $this->name = $template->name;
        $this->description = $template->description;
        $this->working_days = $template->working_days ?? [];
        $this->is_default = $template->is_default;
        $this->status = $template->status?->value ?? 'draft';
        $this->academic_year_id = $template->academic_year_id;
        $this->educational_stage_id = $template->educational_stage_id;

        // تحميل الصفوف
        $this->grade_ids = $template->grades->pluck('id')->toArray();

        // تحميل الحصص
        $this->slots = $template->timeSlots->map(fn($s) => [
            'id' => $s->id,
            'day_of_week' => $s->day_of_week,
            'label' => $s->label,
            'order_index' => $s->order_index,
            'start_time' => $s->start_time?->format('H:i') ?? '',
            'end_time' => $s->end_time?->format('H:i') ?? '',
            'type' => $s->type?->value ?? 'academic',
            'is_attendance_checkpoint' => $s->is_attendance_checkpoint,
        ])->toArray();
    }

    /**
     * إعادة تعيين النموذج
     */
    public function resetForm(): void
    {
        $this->reset();
        $this->working_days = [0, 1, 2, 3, 4]; // الأحد - الخميس
        $this->status = 'draft';
        $this->generator_breaks = [
            ['after_slot' => 2, 'duration' => 15],
            ['after_slot' => 4, 'duration' => 20],
        ];
    }

    /**
     * تحويل إلى DTO
     */
    public function toData(): TimetableTemplateData
    {
        return TimetableTemplateData::fromLivewireForm(
            formData: [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'working_days' => $this->working_days,
                'is_default' => $this->is_default,
                'status' => $this->status,
                'academic_year_id' => $this->academic_year_id,
                'educational_stage_id' => $this->educational_stage_id,
            ],
            slotsData: $this->slots,
            gradeIds: $this->grade_ids,
        );
    }

    /**
     * إعدادات المولد الذكي
     */
    public function getGeneratorConfig(): SlotGeneratorConfig
    {
        return SlotGeneratorConfig::fromArray([
            'day_start_time' => $this->generator_start_time,
            'slot_duration' => $this->generator_slot_duration,
            'number_of_slots' => $this->generator_number_of_slots,
            'breaks' => $this->generator_breaks,
            'include_assembly' => $this->generator_include_assembly,
            'assembly_duration' => $this->generator_assembly_duration,
        ]);
    }

    /**
     * جلب حصص يوم معين
     */
    public function getSlotsForDay(int $dayOfWeek): array
    {
        return collect($this->slots)
            ->filter(fn($s) => ($s['day_of_week'] ?? 0) === $dayOfWeek)
            ->sortBy('order_index')
            ->values()
            ->toArray();
    }

    /**
     * تعيين حصص ليوم معين
     */
    public function setSlotsForDay(int $dayOfWeek, array $daySlots): void
    {
        // حذف حصص اليوم القديمة
        $this->slots = collect($this->slots)
            ->reject(fn($s) => ($s['day_of_week'] ?? 0) === $dayOfWeek)
            ->values()
            ->toArray();

        // إضافة الحصص الجديدة
        foreach ($daySlots as $slot) {
            $slot['day_of_week'] = $dayOfWeek;
            $this->slots[] = $slot;
        }
    }

    /**
     * إضافة استراحة للمولد
     */
    public function addBreak(): void
    {
        $this->generator_breaks[] = [
            'after_slot' => count($this->generator_breaks) + 2,
            'duration' => 15,
        ];
    }

    /**
     * حذف استراحة من المولد
     */
    public function removeBreak(int $index): void
    {
        unset($this->generator_breaks[$index]);
        $this->generator_breaks = array_values($this->generator_breaks);
    }
}
