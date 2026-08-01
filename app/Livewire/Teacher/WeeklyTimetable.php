<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Calendar\Services\SchoolCalendarService;
use App\Domains\Academic\Attendance\Services\AttendanceSettingsService;

use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class WeeklyTimetable extends Component
{
    public function render(
        \App\Domains\Academic\Attendance\Services\AttendanceLookupService $attendanceService,
        AttendanceSettingsService $attendanceSettings
    ) {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return view('livewire.teacher.weekly-timetable', [
                'weeklySchedule' => [],
                'dayNames' => [],
                'attendanceStatus' => [],
                'weekDates' => [],
                'canTakeAttendance' => false,
                'allTimeSlots' => collect(),
                'workingDays' => [],
                'gridLookup' => [],
            ]);
        }

        // Get Active Academic Year
        $academicYear = school()->activeYear();
        $responsibleRole = 'subject_teacher';
        if ($academicYear) {
            $settings = $attendanceSettings->getSettings($academicYear->id);
            $responsibleRole = $settings->responsible_role->value ?? 'subject_teacher';
        }
        $canTakeAttendance = $responsibleRole !== 'admin_staff';
        $termId = school()->activeTerm()?->id;

        // ✅ PR-6: Calculate week dates based on weekend_days from academic year
        $calendarService = app(SchoolCalendarService::class);
        $weekendDays = $academicYear ? $calendarService->getWeekendDaysForYear($academicYear->id) : [0, 6];

        // Calculate working days (opposite of weekend)
        $workingDays = array_values(array_diff([0, 1, 2, 3, 4, 5, 6], $weekendDays));

        // Week boundaries: first and last working day
        $weekStartDay = min($workingDays);
        $weekEndDay = max($workingDays);

        // Calculate week dates based on working days
        $startOfWeek = now()->startOfWeek($weekStartDay);
        $endOfWeek = now()->endOfWeek($weekEndDay);

        // Fetch Weekly Schedule
        $timetableEntries = Timetable::query()
            ->select('id', 'class_section_id', 'course_offering_id', 'time_slot_id')
            ->with([
                'classSection:id,name,grade_id',
                'classSection.grade:id,name',
                'courseOffering:id,subject_id',
                'courseOffering.subject:id,name',
                'timeSlot:id,day_of_week,start_time,end_time,order_index,label'
            ])
            ->when(
                $responsibleRole === 'class_teacher',
                fn($q) => $q->whereHas(
                    'classSection',
                    fn($sub) => $sub
                        ->where('homeroom_teacher_id', $teacher->id)
                        ->where('academic_year_id', $academicYear?->id)
                ),
                fn($q) => $q->whereHas(
                    'courseOffering',
                    fn($sub) => $sub
                        ->where('teacher_id', $teacher->id)
                        ->where('academic_year_id', $academicYear?->id)
                )
            )
            ->where('term_id', $termId)
            ->get()
            ->sortBy(['timeSlot.order_index', 'timeSlot.day_of_week']);

        // Group by day (for backward compatibility)
        $weeklySchedule = $timetableEntries
            ->sortBy(['timeSlot.day_of_week', 'timeSlot.order_index'])
            ->groupBy(fn($t) => $t->timeSlot->day_of_week);

        // Build a lookup: [day_of_week][order_index] => session
        $gridLookup = [];
        foreach ($timetableEntries as $entry) {
            $day = $entry->timeSlot->day_of_week;
            $order = $entry->timeSlot->order_index;
            $gridLookup[$day][$order] = $entry;
        }

        // Extract unique time slots sorted by order_index (for grid rows)
        $allTimeSlots = $timetableEntries
            ->pluck('timeSlot')
            ->unique('order_index')
            ->sortBy('order_index')
            ->values();

        // Get Attendance Status
        $attendanceStatus = $attendanceService->getWeeklyAttendanceStatus(
            $teacher->id,
            $startOfWeek->format('Y-m-d'),
            $endOfWeek->format('Y-m-d'),
            $termId,
            $academicYear?->id
        );

        // Map dates to days for the view
        $weekDates = [];
        for ($i = 0; $i <= 6; $i++) {
            $weekDates[$i] = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
        }

        // Day Names
        $dayNames = collect(DayOfWeek::cases())->mapWithKeys(fn($day) => [
            $day->value => $day->label()
        ]);

        return view('livewire.teacher.weekly-timetable', [
            'weeklySchedule' => $weeklySchedule,
            'dayNames' => $dayNames,
            'attendanceStatus' => $attendanceStatus,
            'weekDates' => $weekDates,
            'canTakeAttendance' => $canTakeAttendance,
            'allTimeSlots' => $allTimeSlots,
            'workingDays' => $workingDays,
            'gridLookup' => $gridLookup,
        ]);
    }
}
