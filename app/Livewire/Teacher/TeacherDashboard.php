<?php

namespace App\Livewire\Teacher;

use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Infrastructure\Context\AcademicContextService;
use App\Domains\HR\Teacher\Services\TeacherLookupService;
use App\Domains\Academic\Attendance\Services\AttendanceSettingsService;
use App\Domains\Shared\Enums\DayOfWeek;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

#[Layout('layouts.app')]
class TeacherDashboard extends Component
{
    // بيانات من الكاش (Injected)
    public $activeYear = null;
    public $activeTerm = null;
    public $attendanceMode = 'per_period';
    public $responsibleRole = 'subject_teacher';

    // بيانات المعلم
    public ?int $teacherId = null;
    public ?Teacher $teacher = null;

    public function mount(
        AcademicContextService $context,
        TeacherLookupService $teacherLookup,
        AttendanceSettingsService $attendanceSettings
    ) {
        abort_unless(auth()->user()->can('teacher.dashboard'), 403, 'ليس لديك صلاحية الوصول للوحة المعلم.');

        // 1. البيانات الثابتة من الكاش
        $this->activeYear = $context->activeYear();
        $this->activeTerm = $context->activeTerm();
        if ($this->activeYear && !$this->activeTerm) {
            $this->dispatch('error', message: 'يجب تفعيل ترم قبل متابعة لوحة المعلم.');
        }

        // 2. معرف المعلم (مخزن في الكاش)
        $userId = auth()->id();
        $this->teacherId = $teacherLookup->getTeacherIdForUser($userId);

        if ($this->teacherId) {
            $this->teacher = Teacher::with('staff')->find($this->teacherId);
        }

        // 3. إعدادات الحضور
        if ($this->activeYear) {
            $settings = $attendanceSettings->getSettings($this->activeYear->id);
            if ($settings) {
                $this->attendanceMode = $settings->mode->value ?? 'per_period';
                $this->responsibleRole = $settings->responsible_role->value ?? 'subject_teacher';
            }
        }
    }

    /**
     * ==========================================
     *  Timeline Today - الحصص مع حالتها
     * ==========================================
     * الحالات:
     * - DONE: تم الرصد (أزرق)
     * - ACTIVE: جارية الآن (أخضر)
     * - MISSED: فات وقتها ولم تُرصد (أحمر)
     * - UPCOMING: قادمة (رمادي)
     */
    #[Computed]
    public function canTakeAttendance(): bool
    {
        if (!$this->teacherId || !$this->activeYear) {
            return false;
        }

        if ($this->responsibleRole === 'admin_staff') {
            return false;
        }

        if ($this->responsibleRole === 'class_teacher') {
            return ClassSection::query()
                ->where('academic_year_id', $this->activeYear->id)
                ->where('homeroom_teacher_id', $this->teacherId)
                ->exists();
        }

        return true;
    }

    #[Computed]
    public function todaysTimeline()
    {
        if (!$this->teacherId || !$this->canTakeAttendance)
            return collect();

        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $today = $now->format('Y-m-d');

        // استعلام محسّن: Eager Loading + Select Specific Columns
        $query = Timetable::query()
            ->select('id', 'class_section_id', 'course_offering_id', 'time_slot_id')
            ->with([
                'classSection:id,name,grade_id',
                'classSection.grade:id,name',
                'courseOffering:id,subject_id,teacher_id',
                'courseOffering.subject:id,name',
                'timeSlot:id,start_time,end_time,order_index,label,day_of_week'
            ]);

        // تصفية حسب المسؤولية
        if ($this->responsibleRole === 'class_teacher') {
            $query->whereHas(
                'classSection',
                fn($q) =>
                $q->where('homeroom_teacher_id', $this->teacherId)
            );
        } else {
            $query->whereHas(
                'courseOffering',
                fn($q) =>
                $q->where('teacher_id', $this->teacherId)
            );
        }

        // فلترة اليوم فقط
        $query->whereHas('timeSlot', fn($q) => $q->where('day_of_week', $dayOfWeek));

        // فلترة حسب نمط الحضور
        if ($this->attendanceMode === 'daily_only') {
            $query->whereHas('timeSlot', fn($q) => $q->where('order_index', 0));
        } elseif ($this->attendanceMode === 'checkpoints') {
            $query->whereHas('timeSlot', fn($q) => $q->where('is_attendance_checkpoint', true));
        }

        // ✅ PR0: Scoped to Active Term
        $query->where('term_id', $this->activeTerm?->id);

        $timetables = $query->get();

        // جلب سجلات الحضور لليوم مرة واحدة (Single Query)
        $attendedSlots = Attendance::where('date', $today)
            ->whereIn('time_slot_id', $timetables->pluck('time_slot_id'))
            ->distinct('time_slot_id')
            ->pluck('time_slot_id')
            ->flip()
            ->toArray();

        // حساب الحالة لكل حصة
        return $timetables
            ->sortBy('timeSlot.order_index')
            ->map(function ($timetable) use ($attendedSlots, $now, $today) {
                $startAt = $timetable->timeSlot?->getStartTimeCarbon($today);
                $endAt = $timetable->timeSlot?->getEndTimeCarbon($today);
                $slotId = $timetable->time_slot_id;

                // منطق الحالة (Priority Order)
                if (isset($attendedSlots[$slotId])) {
                    $status = 'DONE';
                } elseif ($startAt && $endAt && $now->betweenIncluded($startAt, $endAt)) {
                    $status = 'ACTIVE';
                } elseif ($endAt && $now->greaterThan($endAt)) {
                    $status = 'MISSED';
                } else {
                    $status = 'UPCOMING';
                }

                return [
                    'id' => $timetable->id,
                    'subject' => $timetable->courseOffering?->subject?->name ?? 'غير محدد',
                    'section' => $timetable->classSection?->full_name ?? '',
                    'start_time' => $startAt?->format('H:i') ?? $timetable->timeSlot?->start_time,
                    'end_time' => $endAt?->format('H:i') ?? $timetable->timeSlot?->end_time,
                    'label' => $timetable->timeSlot->label,
                    'status' => $status,
                ];
            });
    }

    /**
     * ==========================================
     *  Stats - الإحصائيات السريعة
     * ==========================================
     */
    #[Computed]
    public function stats()
    {
        $timeline = $this->todaysTimeline;

        return [
            'total' => $timeline->count(),
            'done' => $timeline->where('status', 'DONE')->count(),
            'active' => $timeline->where('status', 'ACTIVE')->count(),
            'missed' => $timeline->where('status', 'MISSED')->count(),
            'upcoming' => $timeline->where('status', 'UPCOMING')->count(),
        ];
    }

    /**
     * ==========================================
     *  My Classes - الفصول التي أدرسها (محسّن)
     * ==========================================
     */
    #[Computed]
    public function myClassSections()
    {
        if (!$this->teacherId)
            return collect();

        return CourseOffering::query()
            ->select('id', 'class_section_id', 'subject_id', 'teacher_id', 'term_id')
            ->with([
                'classSection' => fn($q) => $q->select('id', 'name', 'grade_id')->withCount('students'),
                'classSection.grade:id,name',
                'subject:id,name'
            ])
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYear?->id)
            ->where(function ($q) {
                $q->where('term_id', $this->activeTerm?->id)
                    ->orWhereNull('term_id');
            })
            ->get()
            ->map(fn($offering) => [
                'section' => $offering->classSection,
                'subject' => $offering->subject,
                'student_count' => $offering->classSection?->students_count ?? 0,
            ]);
    }

    /**
     * ==========================================
     *  Grid Data - بيانات الجدول (Grid)
     * ==========================================
     */
    #[Computed]
    public function timeSlots()
    {
        // نفترض وجود قالب واحد نشط للسنة
        return \App\Domains\Academic\Timetable\Models\TimeSlot::whereHas('template', function ($q) {
            $q->where('status', 'active')
                ->where('academic_year_id', $this->activeYear?->id);
        })
            ->orderBy('order_index')
            ->get();
    }

    #[Computed]
    public function timetableMatrix()
    {
        if (!$this->teacherId)
            return [];

        $entries = Timetable::query()
            ->select('id', 'class_section_id', 'course_offering_id', 'time_slot_id')
            ->with([
                'classSection' => function ($q) {
                    $q->select('id', 'name', 'grade_id')
                        ->with('grade:id,name');
                },
                'courseOffering' => function ($q) {
                    $q->select('id', 'subject_id')
                        ->with('subject:id,name');
                },
                'timeSlot:id,day_of_week,order_index'
            ])
            ->when(
                $this->responsibleRole === 'class_teacher',
                fn($q) => $q->whereHas(
                    'classSection',
                    fn($sub) => $sub
                        ->where('homeroom_teacher_id', $this->teacherId)
                        ->where('academic_year_id', $this->activeYear?->id)
                ),
                fn($q) => $q->whereHas(
                    'courseOffering',
                    fn($sub) => $sub
                        ->where('teacher_id', $this->teacherId)
                        ->where('academic_year_id', $this->activeYear?->id)
                )
            )
            ->where('term_id', $this->activeTerm?->id) // ✅ PR0: Scoped to Active Term
            ->get();

        // تنظيم البيانات: [day][order_index] => Entry
        $matrix = [];
        foreach ($entries as $entry) {
            $day = $entry->timeSlot->day_of_week;
            $order = $entry->timeSlot->order_index;
            $matrix[$day][$order] = $entry;
        }

        return $matrix;
    }

    /**
     * أيام الأسبوع (للعرض)
     */
    #[Computed]
    public function days()
    {
        return DayOfWeek::cases();
    }

    /**
     * أسماء الأيام
     */
    #[Computed]
    public function dayNames()
    {
        return collect(DayOfWeek::cases())->mapWithKeys(fn($day) => [
            $day->value => $day->label()
        ]);
    }

    public function render()
    {
        return view('livewire.teacher.teacher-dashboard');
    }
}
