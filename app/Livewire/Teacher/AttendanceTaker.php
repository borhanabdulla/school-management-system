<?php

namespace App\Livewire\Teacher;

use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Attendance\Services\AttendanceLookupService;
use App\Domains\Academic\Attendance\Services\AttendanceSettingsService;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
use App\Domains\Academic\Attendance\Actions\RecordStudentAttendanceAction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

#[Layout('layouts.app')]
class AttendanceTaker extends Component
{
    #[Locked]
    public Timetable $timetable;
    public $date;

    // مصفوفة البيانات التي ستعرض وتعدل في الواجهة
    public $students = [];

    public $isHoliday = false;

    // هل تم رصد هذه الحصة مسبقاً؟
    public bool $hasExistingRecords = false;

    public function mount($timetableId, AttendanceLookupService $service)
    {
        $this->date = now()->format('Y-m-d');

        // تحميل الجدول مع العلاقات الضرورية
        $this->timetable = Timetable::with(['classSection', 'courseOffering.subject', 'timeSlot'])
            ->findOrFail($timetableId);

        // ✅ PR0: Security Check - Ensure Timetable belongs to Active Term
        $activeTermId = school()->activeTerm()?->id;
        if ($this->timetable->term_id && $activeTermId && $this->timetable->term_id != $activeTermId) {
            abort(403, __('attendance.cannot_take_attendance_non_active_term'));
        }

        $teacher = auth()->user()?->teacher;
        if (!$teacher) {
            abort(403, 'لا يوجد صلاحية لرصد الحضور لهذا الحساب.');
        }

        $academicYearId = $this->timetable->classSection?->academic_year_id
            ?? $this->timetable->courseOffering?->academic_year_id
            ?? school()->activeYearId();

        if ($academicYearId) {
            $settings = app(AttendanceSettingsService::class)->getSettings($academicYearId);
            $role = $settings->responsible_role->value ?? AttendanceResponsibility::SubjectTeacher->value;

            if ($role === AttendanceResponsibility::AdminStaff->value) {
                abort(403, 'رصد الحضور مخصص للإداريين فقط.');
            }

            if ($role === AttendanceResponsibility::HomeroomTeacher->value
                && $this->timetable->classSection?->homeroom_teacher_id !== $teacher->id) {
                abort(403, 'لا تملك صلاحية رصد حضور هذه الشعبة.');
            }

            if ($role === AttendanceResponsibility::SubjectTeacher->value
                && $this->timetable->courseOffering?->teacher_id !== $teacher->id) {
                abort(403, 'لا تملك صلاحية رصد حضور هذه الحصة.');
            }
        }

        // ✅ PR1: Check Calendar for Holidays
        $calendarService = app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class);
        $isHoliday = $academicYearId
            ? $calendarService->isHolidayForYear($this->date, $academicYearId)
            : $calendarService->isHoliday($this->date);

        if ($isHoliday) {
            $this->isHoliday = true;
            session()->flash('warning', __('attendance.holiday_warning'));
        }

        if (!$this->isHoliday) {
            // جلب البيانات من السيرفس وتحويلها لمصفوفة قابلة للتعديل
            $this->students = $service->getAttendanceSheetData($this->timetable, $this->date)->toArray();

            // تحقق إذا كان هناك سجلات محفوظة مسبقاً
            $this->hasExistingRecords = collect($this->students)->contains('is_saved_previously', true);
        }
    }

    /**
     * عدد الحاضرين
     */
    #[Computed]
    public function presentCount(): int
    {
        return collect($this->students)->where('status', 'present')->count();
    }

    /**
     * عدد الغائبين
     */
    #[Computed]
    public function absentCount(): int
    {
        return collect($this->students)->where('status', 'absent')->count();
    }

    /**
     * عدد المتأخرين
     */
    #[Computed]
    public function lateCount(): int
    {
        return collect($this->students)->where('status', 'late')->count();
    }

    /**
     * عدد المعذورين
     */
    #[Computed]
    public function excusedCount(): int
    {
        return collect($this->students)->where('status', 'excused')->count();
    }

    /**
     * تحديد الكل حاضر بضغطة واحدة
     */
    public function setAllPresent()
    {
        foreach ($this->students as $index => $student) {
            $this->students[$index]['status'] = 'present';
            $this->students[$index]['delay_minutes'] = 0;
        }

        $this->dispatch('notify', message: __('attendance.all_set_present'), type: 'info');
    }

    /**
     * إعادة تعيين الكل للحالة الافتراضية
     */
    public function resetAll()
    {
        foreach ($this->students as $index => $student) {
            $this->students[$index]['status'] = 'present';
            $this->students[$index]['delay_minutes'] = 0;
            $this->students[$index]['remarks'] = '';
        }
    }

    public function save(RecordStudentAttendanceAction $action)
    {
        if ($this->isHoliday)
            return;

        // الحفظ
        $action->execute(
            $this->timetable,
            $this->date,
            $this->students,
            auth()->id()
        );

        // تحديث حالة الحفظ
        $this->hasExistingRecords = true;
        foreach ($this->students as $index => $student) {
            $this->students[$index]['is_saved_previously'] = true;
        }

        $this->dispatch('notify', message: __('attendance.attendance_saved_successfully'), type: 'success');

        return redirect()->route('teacher.dashboard')->with('message', __('attendance.attendance_recorded_successfully'));
    }

    // لتسهيل التغيير السريع للحالة
    public function setStatus($index, $status)
    {
        $this->students[$index]['status'] = $status;

        // تصفير الدقائق إذا لم يكن متأخراً
        if ($status !== 'late') {
            $this->students[$index]['delay_minutes'] = 0;
        }
    }

    public function render()
    {
        return view('livewire.teacher.attendance-taker');
    }
}
