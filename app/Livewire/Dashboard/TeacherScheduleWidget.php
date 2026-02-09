<?php

namespace App\Livewire\Dashboard;

use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Livewire\Component;
use Livewire\Attributes\Computed;

class TeacherScheduleWidget extends Component
{
    public $timetables = [];
    public $attendanceMode = 'checkpoints'; // default

    public function mount()
    {
        $this->loadAttendanceSettings();
        $this->loadSchedule();
    }

    public $responsibleRole = 'subject_teacher'; // default

    protected function loadAttendanceSettings()
    {
        $activeYear = school()->activeYear();
        if ($activeYear) {
            $settings = AttendanceSetting::where('academic_year_id', $activeYear->id)->first();
            $this->attendanceMode = $settings?->mode->value ?? 'checkpoints';
            $this->responsibleRole = $settings?->responsible_role->value ?? 'subject_teacher';
        }
    }

    public function loadSchedule()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return;
        }

        $dayOfWeek = now()->dayOfWeek;
        $today = now()->format('Y-m-d');
        $query = Timetable::query();

        // 1. تحديد النطاق بناءً على المسؤولية
        if ($this->responsibleRole === 'class_teacher') {
            // مربي الفصل: يرى جداول الفصول التي هو مربيها
            $query->whereHas('classSection', function ($q) use ($teacher) {
                $q->where('homeroom_teacher_id', $teacher->id);
            });
        } else {
            // معلم المادة (الافتراضي): يرى حصصه الخاصة
            $query->whereHas('courseOffering', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            });
        }

        // 2. فلترة اليوم
        $query->whereHas('timeSlot', function ($q) use ($dayOfWeek) {
            $q->where('day_of_week', $dayOfWeek);
        })
            ->with(['classSection', 'courseOffering.subject', 'timeSlot']);

        // 3. فلترة حسب نمط الحضور
        if ($this->attendanceMode === 'daily_only') {
            $query->whereHas('timeSlot', function ($q) {
                $q->where('order_index', 0);
            });
        } elseif ($this->attendanceMode === 'checkpoints') {
            $query->whereHas('timeSlot', function ($q) {
                $q->where('is_attendance_checkpoint', true);
            });
        }

        $timetables = $query->get()->sortBy('timeSlot.start_time');

        // إضافة حالة الرصد لكل حصة
        $this->timetables = $timetables->map(function ($timetable) use ($today) {
            $isRecorded = Attendance::where('class_section_id', $timetable->class_section_id)
                ->where('date', $today)
                ->where('time_slot_id', $timetable->time_slot_id)
                ->exists();

            $timetable->is_recorded = $isRecorded;
            return $timetable;
        });
    }

    #[Computed]
    public function recordedCount(): int
    {
        return collect($this->timetables)->where('is_recorded', true)->count();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return collect($this->timetables)->where('is_recorded', false)->count();
    }

    public function render()
    {
        return view('livewire.dashboard.teacher-schedule-widget');
    }
}
