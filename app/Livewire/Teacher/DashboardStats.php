<?php

namespace App\Livewire\Teacher;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\Timetable\Models\Timetable;
use Livewire\Component;

class DashboardStats extends Component
{
    public $totalClasses = 0;
    public $pendingClasses = 0;
    public $attendanceRate = 0;
    public $attendanceMode = 'checkpoints';
    public $responsibleRole = 'subject_teacher';

    public function mount()
    {
        $this->loadSettings();
        $this->calculateStats();
    }

    protected function loadSettings()
    {
        $activeYear = school()->activeYear();
        if ($activeYear) {
            $settings = AttendanceSetting::where('academic_year_id', $activeYear->id)->first();
            $this->attendanceMode = $settings?->mode->value ?? 'checkpoints';
            $this->responsibleRole = $settings?->responsible_role->value ?? 'subject_teacher';
        }
    }

    protected function calculateStats()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return;
        }

        $dayOfWeek = now()->dayOfWeek;
        $today = now()->format('Y-m-d');
        $query = Timetable::query();

        // 1. Filter by Responsibility
        if ($this->responsibleRole === 'class_teacher') {
            $query->whereHas('classSection', function ($q) use ($teacher) {
                $q->where('homeroom_teacher_id', $teacher->id);
            });
        } else {
            $query->whereHas('courseOffering', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            });
        }

        // 2. Filter by Day
        $query->whereHas('timeSlot', function ($q) use ($dayOfWeek) {
            $q->where('day_of_week', $dayOfWeek);
        });

        // 3. Filter by Mode
        if ($this->attendanceMode === 'daily_only') {
            $query->whereHas('timeSlot', function ($q) {
                $q->where('order_index', 0);
            });
        } elseif ($this->attendanceMode === 'checkpoints') {
            $query->whereHas('timeSlot', function ($q) {
                $q->where('is_attendance_checkpoint', true);
            });
        }

        $timetables = $query->get();
        $this->totalClasses = $timetables->count();

        // Calculate Pending
        $recordedCount = 0;
        foreach ($timetables as $timetable) {
            $isRecorded = Attendance::where('class_section_id', $timetable->class_section_id)
                ->where('date', $today)
                ->where('time_slot_id', $timetable->time_slot_id)
                ->exists();

            if ($isRecorded) {
                $recordedCount++;
            }
        }
        $this->pendingClasses = $this->totalClasses - $recordedCount;

        // Calculate Attendance Rate (Today)
        // Rate = (Present Students / Total Students in Recorded Sessions) * 100
        // If no sessions recorded today, maybe show weekly rate? Let's stick to today for now.

        if ($recordedCount > 0) {
            $attendanceRecords = Attendance::where('date', $today)
                ->whereIn('class_section_id', $timetables->pluck('class_section_id'))
                ->whereIn('time_slot_id', $timetables->pluck('time_slot_id'))
                ->get();

            $totalStudents = $attendanceRecords->count();
            $presentStudents = $attendanceRecords->whereIn('status', ['present', 'late'])->count();

            $this->attendanceRate = $totalStudents > 0
                ? round(($presentStudents / $totalStudents) * 100, 1)
                : 0;
        } else {
            $this->attendanceRate = 0;
        }
    }

    public function render()
    {
        return view('livewire.teacher.dashboard-stats');
    }
}
