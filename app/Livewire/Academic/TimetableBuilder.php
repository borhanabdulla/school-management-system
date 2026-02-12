<?php

namespace App\Livewire\Academic;

use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Actions\DeleteTimetableEntryAction;
use App\Infrastructure\Context\AcademicContextService;
use App\Domains\Academic\Term\Services\TermLookupService;
use App\Domains\Academic\AcademicYear\Services\AcademicYearService;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Academic\Subject\Services\SubjectLookupService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class TimetableBuilder extends Component
{
    // فلاتر البحث
    public $selectedYearId;
    public $selectedTermId;
    public $selectedGradeId;
    public $selectedSectionId;

    // حالة المودال
    public $showModal = false;
    public $selectedSlotId = null;
    public $selectedDay = null;

    // اختيارات المودال (منفصلة)
    public $selectedTeacherId = null;
    public $selectedSubjectId = null;

    // بحث
    public $searchTeacher = '';
    public $searchSubject = '';

    // تحذير التعارض
    public $conflictWarning = null;
    public $forceAdd = false;



    public function mount()
    {
        $context = app(AcademicContextService::class);
        $activeYear = $context->activeYear();
        $this->selectedYearId = $activeYear?->id;

        if ($activeYear) {
            $currentTerm = $context->activeTerm();
            $this->selectedTermId = $currentTerm?->id;
            if (!$this->selectedTermId) {
                $this->dispatch('error', message: 'يجب تفعيل ترم قبل بناء الجدول.');
            }
        }
    }

    #[Computed]
    public function academicYears()
    {
        return app(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService::class)->getList();
    }

    #[Computed]
    public function terms()
    {
        if (!$this->selectedYearId)
            return collect();

        return app(TermLookupService::class)->search([
            'year_id' => $this->selectedYearId
        ]);
    }

    #[Computed]
    public function grades()
    {
        return app(GradeLookupService::class)->getGradesList();
    }

    #[Computed]
    public function sections()
    {
        if (!$this->selectedGradeId)
            return collect();

        $query = ClassSection::where('grade_id', $this->selectedGradeId);

        if ($this->selectedYearId) {
            $query->where('academic_year_id', $this->selectedYearId);
        }

        return $query->get();
    }

    /**
     * جلب القالب المناسب للصف
     */
    #[Computed]
    public function template()
    {
        if (!$this->selectedGradeId || !$this->selectedYearId)
            return null;

        // 1. ابحث عن قالب مُعين للصف
        $assignment = DB::table('grade_timetable_template')
            ->where('grade_id', $this->selectedGradeId)
            ->where('academic_year_id', $this->selectedYearId)
            ->first();

        if ($assignment) {
            return TimetableTemplate::find($assignment->template_id);
        }

        // 2. Fallback: القالب الافتراضي للسنة
        return TimetableTemplate::where('academic_year_id', $this->selectedYearId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * أيام العمل من القالب
     */
    #[Computed]
    public function workingDays(): array
    {
        if (!$this->template) {
            return [0, 1, 2, 3, 4]; // الأحد - الخميس
        }
        return $this->template->working_days ?? [0, 1, 2, 3, 4];
    }

    /**
     * أسماء الأيام للعرض
     */
    #[Computed]
    public function dayNames(): array
    {
        $names = [];
        foreach ($this->workingDays as $day) {
            $names[$day] = DayOfWeek::tryFrom((int) $day)?->label() ?? "يوم {$day}";
        }
        return $names;
    }

    /**
     * جلب جميع الحصص للقالب مرة واحدة (Optimization)
     */
    #[Computed]
    public function allTemplateSlots()
    {
        if (!$this->template)
            return collect();

        return $this->template->timeSlots()
            ->orderBy('order_index')
            ->get();
    }

    /**
     * صفوف الحصص (من يوم واحد كـ template للصفوف)
     * تشمل الفسحات والطابور
     */
    #[Computed]
    public function slotRows()
    {
        if ($this->allTemplateSlots->isEmpty())
            return collect();

        $firstDay = $this->workingDays[0] ?? 0;

        return $this->allTemplateSlots
            ->where('day_of_week', $firstDay)
            ->sortBy('order_index');
    }

    /**
     * خريطة الحصص: day_of_week => order_index => TimeSlot
     */
    #[Computed]
    public function slotsMap()
    {
        if ($this->allTemplateSlots->isEmpty())
            return collect();

        return $this->allTemplateSlots
            ->groupBy('day_of_week')
            ->map(fn($slots) => $slots->keyBy('order_index'));
    }

    /**
     * جلب الحصة الفعلية لليوم والترتيب
     */
    public function getSlotForDayAndOrder(int $day, int $orderIndex): ?TimeSlot
    {
        return $this->slotsMap[$day][$orderIndex] ?? null;
    }

    /**
     * مصفوفة الجدول الدراسي: slot_id => Timetable entry
     * (Optimized with Nested Eager Loading)
     */
    #[Computed]
    public function timetableMatrix()
    {
        if (!$this->selectedSectionId)
            return collect();

        // ✅ PR0: فلترة الجدول حسب الترم المختار (أو الحالي)
        $termId = $this->selectedTermId ?? app(AcademicContextService::class)->activeTerm()?->id;

        if (!$termId)
            return collect();

        return Timetable::with([
            'courseOffering' => function ($q) {
                $q->select('id', 'subject_id', 'teacher_id');
            },
            'courseOffering.subject:id,name', // Select specific columns
            'courseOffering.teacher.staff:id,first_name,last_name' // Select specific columns
        ])
            ->where('class_section_id', $this->selectedSectionId)
            ->where('term_id', $termId) // ✅ PR0: Filter by Term ID
            ->get()
            ->keyBy('time_slot_id');
    }

    /**
     * قائمة المعلمين المتاحين مع فلترة البحث
     */
    #[Computed]
    public function teachers()
    {
        $query = Teacher::with('staff:id,first_name,last_name');

        if ($this->searchTeacher) {
            $search = $this->searchTeacher;
            $query->whereHas('staff', fn($q) => $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"));
        }

        return $query->limit(20)->get();
    }

    /**
     * قائمة المواد المتاحة للصف المحدد
     */
    #[Computed]
    public function subjects()
    {
        if (!$this->selectedGradeId)
            return collect();

        $subjects = app(SubjectLookupService::class)->getSubjectsList();

        if ($this->searchSubject) {
            $subjects = $subjects->filter(function ($subject) {
                return str_contains($subject->name, $this->searchSubject);
            });
        }

        return $subjects;
    }

    public function updatedSelectedYearId($value)
    {
        $this->selectedTermId = null;
        $this->selectedGradeId = null;
        $this->selectedSectionId = null;

        if ($value) {
            $currentTerm = app(\App\Domains\Academic\Term\Services\TermLookupService::class)
                ->search(['year_id' => $value])
                ->where('status', \App\Domains\Academic\Term\Enums\TermStatus::Active)
                ->first();
            $this->selectedTermId = $currentTerm?->id;
        }
    }

    public function updatedSelectedGradeId($value)
    {
        $this->selectedSectionId = null;
    }

    public function updatedSelectedTeacherId($value)
    {
        $this->conflictWarning = null;
        $this->forceAdd = false;

        if (!$value || !$this->selectedSlotId)
            return;

        $service = app(\App\Domains\Academic\Timetable\Services\TimetableService::class);
        // ✅ PR-3: تمرير السنة المختارة
        $conflict = $service->checkTeacherConflict(
            $value,
            $this->selectedSlotId,
            $this->selectedYearId,
            (int) $this->selectedTermId, // ✅ PR0 Pass Term ID
            $this->selectedSectionId
        );

        if ($conflict) {
            $this->conflictWarning = $conflict;
        }
    }

    public function updatedSelectedSubjectId($value)
    {
        if (!$value || !$this->selectedSectionId || $this->selectedDay === null) {
            return;
        }

        $service = app(\App\Domains\Academic\Timetable\Services\TimetableService::class);
        $conflict = $service->checkSubjectDailyRepetition(
            $value,
            $this->selectedSectionId,
            $this->selectedDay,
            $this->selectedYearId, // ✅ تمرير السنة المختارة
            (int) $this->selectedTermId, // ✅ PR0 Pass Term ID
            $this->selectedSlotId
        );

        if ($conflict) {
            $this->conflictWarning = $conflict;
        } elseif (!$this->conflictWarning || !str_contains($this->conflictWarning['message'], 'المعلم')) {
            $this->conflictWarning = null;
        }
    }

    /**
     * فتح المودال لحصة معينة
     */
    public function openModal($slotId, $day = null)
    {
        $this->selectedSlotId = $slotId;
        $this->selectedDay = $day;
        $this->selectedTeacherId = null;
        $this->selectedSubjectId = null;
        $this->searchTeacher = '';
        $this->searchSubject = '';
        $this->conflictWarning = null;
        $this->forceAdd = false;

        // إذا كانت الحصة موجودة، نحدد القيم تلقائياً
        $matrix = $this->timetableMatrix;
        if (isset($matrix[$slotId])) {
            $existing = $matrix[$slotId];
            $this->selectedTeacherId = $existing->courseOffering?->teacher_id;
            $this->selectedSubjectId = $existing->courseOffering?->subject_id;
        }

        $this->showModal = true;
    }

    

    public function closeModal()
    {
        $this->showModal = false;
        $this->conflictWarning = null;
        $this->forceAdd = false;
    }

    public function confirmConflict()
    {
        $this->forceAdd = true;
        $this->saveSession();
    }

    /**
     * التحقق مما إذا كان الجدول للقراءة فقط (سنوات سابقة أو ترم غير نشط)
     */
    #[Computed]
    public function isReadOnly()
    {
        // 1. Check Year Status
        if (!$this->selectedYearId)
            return true;

        $year = AcademicYear::find($this->selectedYearId);
        $editableYearStatuses = [
            \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active,
            \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Pending,
        ];
        if (!$year || !in_array($year->status, $editableYearStatuses, true)) {
            return true;
        }

        // 2. ✅ PR0: Check Term Status (Strict Read-Only for non-active terms)
        $activeTermId = app(AcademicContextService::class)->activeTerm()?->id;
        if ($this->selectedTermId && $activeTermId && $this->selectedTermId != $activeTermId) {
            // TODO: Allow override with permission 'timetable.edit_non_active_term'
            return true;
        }

        return false;
    }

    public function saveSession()
    {
        if ($this->isReadOnly) {
            $this->dispatch('error', message: __('attendance.timetable_read_only'));
            return;
        }

        if (!$this->selectedTermId) {
            $this->dispatch('error', message: 'يرجى اختيار فصل دراسي قبل حفظ الحصة.');
            return;
        }

        // ✅ PR0 Backend Guard: Prevent modification of non-active terms
        $activeTermId = app(AcademicContextService::class)->activeTerm()?->id;
        if ($this->selectedTermId && $activeTermId && $this->selectedTermId != $activeTermId) {
            abort(403, 'Modification of non-active terms is restricted.');
        }

        $this->validate([
            'selectedTeacherId' => 'required',
            'selectedSubjectId' => 'required',
            'selectedSlotId' => 'required'
        ], [
            'selectedTeacherId.required' => 'يرجى اختيار المعلم',
            'selectedSubjectId.required' => 'يرجى اختيار المادة',
        ]);

        if (!$this->forceAdd) {
            $service = app(\App\Domains\Academic\Timetable\Services\TimetableService::class);
            // ✅ PR-3: تمرير السنة المختارة
            $conflict = $service->checkTeacherConflict(
                $this->selectedTeacherId,
                $this->selectedSlotId,
                $this->selectedYearId,
                (int) $this->selectedTermId, // ✅ PR0 Pass Term ID
                $this->selectedSectionId
            );

            if ($conflict) {
                $this->addError('selectedTeacherId', 'هذا المعلم لديه تعارض! اضغط "استمرار" للتجاوز.');
                return;
            }
        }

        app(\App\Domains\Academic\Timetable\Actions\AssignSessionAction::class)->execute(
            $this->selectedYearId,
            $this->selectedTermId,
            $this->selectedSectionId,
            $this->selectedSubjectId,
            $this->selectedTeacherId,
            $this->selectedSlotId
        );

        $this->closeModal();
        $this->dispatch('notify', message: 'تم حفظ الحصة بنجاح', type: 'success');
    }

    /**
     * حذف حصة من الجدول
     *
     * @security-critical ⚠️  PHASE 2: Added guard to prevent orphaning attendance records
     *
     * @throws CannotDeleteTimetableWithAttendanceException If timetable has attendance records
     */
    public function deleteSession($slotId)
    {
        if ($this->isReadOnly) {
            $this->dispatch('error', message: __('attendance.timetable_read_only'));
            return;
        }

        if (!$this->selectedTermId) {
            $this->dispatch('error', message: 'يرجى اختيار فصل دراسي قبل حذف الحصة.');
            return;
        }

        // ✅ PR0 Backend Guard
        $activeTermId = app(AcademicContextService::class)->activeTerm()?->id;
        if ($this->selectedTermId && $activeTermId && $this->selectedTermId != $activeTermId) {
            abort(403, 'Modification of non-active terms is restricted.');
        }

        $timetables = Timetable::where('class_section_id', $this->selectedSectionId)
            ->where('time_slot_id', $slotId)
            ->where('term_id', $this->selectedTermId)
            ->get();

        foreach ($timetables as $timetable) {
            app(DeleteTimetableEntryAction::class)->execute($timetable->id);
        }

        $this->dispatch('notify', message: 'تم حذف الحصة', type: 'success');
    }

    public function render()
    {
        return view('livewire.academic.timetable-builder')
            ->layout('layouts.app');
    }
}
