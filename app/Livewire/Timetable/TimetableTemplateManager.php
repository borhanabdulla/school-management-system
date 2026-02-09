<?php

namespace App\Livewire\Timetable;

use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Livewire\Forms\Timetable\TimetableTemplateForm;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Services\TimetableLookupService;
use App\Domains\Academic\Timetable\Actions\CreateTimetableTemplateAction;
use App\Domains\Academic\Timetable\Actions\UpdateTimetableTemplateAction;
use App\Domains\Academic\Timetable\Actions\ActivateTimetableTemplateAction;
use App\Domains\Academic\Timetable\Actions\ArchiveTimetableTemplateAction;
use App\Domains\Academic\Timetable\Actions\DeleteTimetableTemplateAction;
use App\Domains\Academic\Timetable\Actions\DuplicateTimetableTemplateAction;
use App\Domains\Academic\Timetable\Exceptions\GradeAlreadyAssignedException;
use App\Domains\Academic\Timetable\Exceptions\TemplateNotEditableException;
use App\Domains\Academic\Timetable\Exceptions\InvalidTimeSlotsException;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class TimetableTemplateManager extends Component
{
    use WithPagination;

    // Form Object
    public TimetableTemplateForm $form;

    // UI State
    public bool $showModal = false;
    public bool $isEditing = false;
    public int $wizardStep = 1;
    public int $activeDay = 0; // اليوم المحدد في التبويبات

    // Filters
    public string $search = '';
    public ?int $filterYearId = null;
    public ?int $filterStageId = null;
    public string $filterStatus = 'all';

    // Service (للقراءة فقط)
    protected TimetableLookupService $lookupService;

    public function boot(TimetableLookupService $lookupService)
    {
        $this->lookupService = $lookupService;
    }

    // Attendance Settings
    public string $attendanceMode = 'checkpoints'; // Default

    public function mount()
    {
        // استخدام school() كمصدر وحيد للحقيقة
        $activeYear = school()->activeYear();
        $this->filterYearId = $activeYear?->id;
        $this->form->academic_year_id = $activeYear?->id;
        $this->form->working_days = school()->workingDays() ?? [0, 1, 2, 3, 4];

        // جلب إعدادات الحضور
        if ($activeYear) {
            $settings = \App\Domains\Academic\Attendance\Models\AttendanceSetting::where('academic_year_id', $activeYear->id)->first();
            $this->attendanceMode = $settings?->mode->value ?? 'checkpoints';
        }
    }

    // ==================== Computed Properties ====================

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::orderByDesc('start_date')->get();
    }

    #[Computed]
    public function stages()
    {
        return EducationalStage::orderBy('rank')->get();
    }

    #[Computed]
    public function grades()
    {
        $query = Grade::with('stage')->orderBy('level_order');

        if ($this->form->educational_stage_id) {
            $query->where('educational_stage_id', $this->form->educational_stage_id);
        }

        return $query->get();
    }

    #[Computed]
    public function days()
    {
        return DayOfWeek::cases();
    }

    #[Computed]
    public function slotTypes()
    {
        return TimeSlotType::cases();
    }

    #[Computed]
    public function templates()
    {
        $query = TimetableTemplate::with(['academicYear', 'educationalStage', 'grades'])
            ->withCount('timeSlots');

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->filterYearId) {
            $query->where('academic_year_id', $this->filterYearId);
        }

        if ($this->filterStageId) {
            $query->where('educational_stage_id', $this->filterStageId);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        return $query->orderByDesc('created_at')->paginate(10);
    }

    /**
     * الصفوف المعينة بالفعل لقوالب أخرى (للتعطيل في الـ Checkbox)
     */
    #[Computed]
    public function assignedGradeIds(): array
    {
        if (!$this->form->academic_year_id)
            return [];

        return \DB::table('grade_timetable_template')
            ->where('academic_year_id', $this->form->academic_year_id)
            ->when($this->form->id, fn($q) => $q->where('template_id', '!=', $this->form->id))
            ->pluck('grade_id')
            ->toArray();
    }

    // ==================== Modal Actions ====================

    public function create()
    {
        $this->form->resetForm();
        $this->form->academic_year_id = $this->filterYearId;
        $this->isEditing = false;
        $this->wizardStep = 1;
        $this->activeDay = 0;
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $template = TimetableTemplate::with(['timeSlots', 'grades'])->findOrFail($id);

        $this->form->setModel($template);
        $this->isEditing = true;
        $this->wizardStep = 1;
        $firstDay = $this->form->working_days[0] ?? 0;
        $this->activeDay = is_string($firstDay)
            ? (\App\Domains\Shared\Enums\DayOfWeek::tryFrom($firstDay)?->value ?? \App\Domains\Shared\Enums\DayOfWeek::fromName($firstDay)->value)
            : $firstDay;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }

    // ==================== Wizard Navigation ====================

    public function nextStep()
    {
        if ($this->wizardStep === 1) {
            $this->form->validate([
                'name' => 'required|string|max:255',
                'academic_year_id' => 'required|exists:academic_years,id',
                'working_days' => 'required|array|min:1',
                'working_days.*' => 'required|integer|between:0,6',
            ]);
        }

        if ($this->wizardStep === 2) {
            // التحقق من وجود حصص
            if (empty($this->form->slots)) {
                $this->addError('slots', 'يجب توليد أو إضافة حصص قبل المتابعة');
                return;
            }
        }

        $this->wizardStep = min(3, $this->wizardStep + 1);

        // تعيين اليوم الأول عند الانتقال للخطوة 2
        if ($this->wizardStep === 2 && !empty($this->form->working_days)) {
            $this->activeDay = $this->form->working_days[0];
        }
    }

    public function previousStep()
    {
        $this->wizardStep = max(1, $this->wizardStep - 1);
    }

    public function goToStep(int $step)
    {
        if ($step <= $this->wizardStep) {
            $this->wizardStep = $step;
        }
    }

    // ==================== Generator Actions ====================

    public function generateSlots()
    {
        if (empty($this->form->working_days)) {
            $this->addError('working_days', 'يجب اختيار أيام العمل أولاً');
            return;
        }

        $config = $this->form->getGeneratorConfig();
        $slots = $this->lookupService->generateSlots($config, $this->form->working_days);

        // تحويل DTOs إلى مصفوفات
        $this->form->slots = collect($slots)->map(fn($s) => $s->toModelArray())->toArray();

        $this->dispatch('notify', message: 'تم توليد ' . count($slots) . ' حصة بنجاح');
    }

    public function applyToAllDays()
    {
        if (empty($this->form->getSlotsForDay($this->activeDay))) {
            $this->addError('slots', 'لا توجد حصص في اليوم الحالي للتطبيق');
            return;
        }

        $sourceSlots = $this->form->getSlotsForDay($this->activeDay);

        foreach ($this->form->working_days as $day) {
            if ($day === $this->activeDay)
                continue;

            $copiedSlots = collect($sourceSlots)->map(function ($slot) use ($day) {
                $newSlot = $slot; // Create a copy
                $newSlot['day_of_week'] = $day;
                $newSlot['id'] = null; // حذف ID للنسخة الجديدة
                return $newSlot;
            })->toArray();

            $this->form->setSlotsForDay($day, $copiedSlots);
        }

        $this->dispatch('notify', message: 'تم تطبيق الحصص على جميع الأيام');
    }

    // ==================== Slot Management ====================

    public function addSlot()
    {
        $daySlots = $this->form->getSlotsForDay($this->activeDay);
        $lastSlot = end($daySlots);

        $startTime = $lastSlot ? $lastSlot['end_time'] : '07:30';
        $endTime = date('H:i', strtotime($startTime) + (45 * 60));

        $this->form->slots[] = [
            'id' => null,
            'day_of_week' => $this->activeDay,
            'label' => 'حصة جديدة',
            'order_index' => count($daySlots),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => 'academic',
            'is_attendance_checkpoint' => false,
        ];
    }

    public function addBreakSlot()
    {
        $daySlots = $this->form->getSlotsForDay($this->activeDay);
        $lastSlot = end($daySlots);

        $startTime = $lastSlot ? $lastSlot['end_time'] : '09:00';
        $endTime = date('H:i', strtotime($startTime) + (15 * 60));

        $this->form->slots[] = [
            'id' => null,
            'day_of_week' => $this->activeDay,
            'label' => 'فسحة',
            'order_index' => count($daySlots),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => 'break',
            'is_attendance_checkpoint' => false,
        ];
    }

    public function removeSlot(int $index)
    {
        unset($this->form->slots[$index]);
        $this->form->slots = array_values($this->form->slots);
    }

    public function updateSlotOrder()
    {
        // إعادة ترتيب الحصص حسب order_index
        $this->form->slots = collect($this->form->slots)
            ->groupBy('day_of_week')
            ->flatMap(function ($daySlots, $day) {
                return $daySlots->sortBy('order_index')->values()->map(function ($slot, $i) {
                    $slot['order_index'] = $i;
                    return $slot;
                });
            })
            ->toArray();
    }

    // ==================== Save Actions ====================

    public function save()
    {
        \Illuminate\Support\Facades\Log::info('TimetableTemplateManager: Save initiated', ['form' => $this->form->all()]);

        try {
            $this->form->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('TimetableTemplateManager: Validation failed', ['errors' => $e->errors()]);
            $this->dispatch('error', message: 'يرجى التحقق من المدخلات (هناك أخطاء في البيانات)');
            throw $e;
        }

        // التحقق من أن السنة الدراسية نشطة أو قيد الإعداد
        $year = AcademicYear::find($this->form->academic_year_id);
        if ($year && !in_array($year->status->value, ['active', 'pending'])) {
            $this->dispatch('error', message: 'عذراً، لا يمكن تعديل قوالب السنوات المغلقة أو المؤرشفة.');
            return;
        }

        try {
            $data = $this->form->toData();
            \Illuminate\Support\Facades\Log::info('TimetableTemplateManager: DTO created', ['data' => $data]);

            if ($this->isEditing) {
                $template = TimetableTemplate::findOrFail($this->form->id);
                app(UpdateTimetableTemplateAction::class)->execute($template, $data);
                $this->dispatch('notify', message: 'تم تحديث القالب بنجاح');
            } else {
                app(CreateTimetableTemplateAction::class)->execute($data);
                $this->dispatch('notify', message: 'تم إنشاء القالب بنجاح');
            }

            $this->closeModal();

        } catch (GradeAlreadyAssignedException $e) {
            $this->addError('grade_ids', $e->getMessage());
        } catch (TemplateNotEditableException $e) {
            $this->addError('status', $e->getMessage());
        } catch (InvalidTimeSlotsException $e) {
            $this->addError('slots', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('TimetableTemplateManager: Save failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    // ==================== Template Actions ====================

    public function activate(int $id)
    {
        try {
            $template = TimetableTemplate::findOrFail($id);
            // السماح بتفعيل القوالب للسنوات النشطة أو قيد الإعداد (Pending)
            if (!in_array($template->academicYear->status->value, ['active', 'pending'])) {
                $this->dispatch('error', message: 'لا يمكن تفعيل قالب لسنة مغلقة أو مؤرشفة.');
                return;
            }
            app(ActivateTimetableTemplateAction::class)->execute($template);
            $this->dispatch('notify', message: 'تم تفعيل القالب');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function archive(int $id)
    {
        try {
            $template = TimetableTemplate::findOrFail($id);
            app(ArchiveTimetableTemplateAction::class)->execute($template);
            $this->dispatch('notify', message: 'تم أرشفة القالب');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    // Safe Deletion State
    public ?int $templateToDelete = null;
    public int $linkedSessionsCount = 0;
    public bool $showDeleteModal = false;

    public function confirmDelete(int $id)
    {
        $this->templateToDelete = $id;
        $template = TimetableTemplate::findOrFail($id);

        // السماح بحذف القوالب للسنوات النشطة أو قيد الإعداد
        if (!in_array($template->academicYear->status->value, ['active', 'pending'])) {
            $this->dispatch('error', message: 'لا يمكن حذف قوالب السنوات السابقة.');
            return;
        }

        // Check for linked sessions (Timetable entries)
        // Assuming the relationship is 'timetableEntries' on TimeSlot, we need to check through slots
        // Or if TimetableTemplate has a direct relationship or we query Timetable model directly
        // Let's check if Timetable model exists and has template_id or if it links via slots.
        // Based on previous context, Timetable entries are linked to TimeSlots.
        // So we count Timetable entries where time_slot_id is in this template's slots.

        $this->linkedSessionsCount = \App\Domains\Academic\Timetable\Models\Timetable::whereIn(
            'time_slot_id',
            $template->timeSlots()->pluck('id')
        )->count();

        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if (!$this->templateToDelete)
            return;

        try {
            $template = TimetableTemplate::findOrFail($this->templateToDelete);

            app(DeleteTimetableTemplateAction::class)->execute($template);

            $this->dispatch('notify', message: 'تم حذف القالب' . ($this->linkedSessionsCount > 0 ? ' وجميع الحصص المرتبطة به' : ''));
            $this->cancelDelete();

        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function cancelDelete()
    {
        $this->templateToDelete = null;
        $this->linkedSessionsCount = 0;
        $this->showDeleteModal = false;
    }

    public function duplicate(int $id)
    {
        try {
            $template = TimetableTemplate::findOrFail($id);
            $newTemplate = app(DuplicateTimetableTemplateAction::class)->execute($template, 'نسخة من ' . $template->name);
            $this->dispatch('notify', message: 'تم نسخ القالب');

            // فتح النسخة للتعديل
            $this->edit($newTemplate->id);
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    // ==================== Render ====================

    public function render()
    {
        return view('livewire.timetable.timetable-template-manager');
    }
}
