<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Calendar\Services\SchoolCalendarService;

use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class WeeklyTimetable extends Component
{
    public function render(\App\Domains\Academic\Attendance\Services\AttendanceLookupService $attendanceService)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return view('livewire.teacher.weekly-timetable', [
                'weeklySchedule' => [],
                'dayNames' => [],
                'attendanceStatus' => [],
                'weekDates' => [],
            ]);
        }

        // Get Active Academic Year
        $academicYear = school()->activeYear();

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

        // Fetch Weekly Schedule (Grouped by Day)
        $weeklySchedule = Timetable::query()
            ->select('id', 'class_section_id', 'course_offering_id', 'time_slot_id')
            ->with([
                'classSection:id,name,grade_id', // Added grade_id for full_name accessor
                'classSection.grade:id,name',    // Eager load grade for full_name
                'courseOffering:id,subject_id',
                'courseOffering.subject:id,name',
                'timeSlot:id,day_of_week,start_time,end_time,order_index,label'
            ])
            ->whereHas(
                'courseOffering',
                fn($q) =>
                $q->where('teacher_id', $teacher->id)
                    ->where('academic_year_id', $academicYear?->id)
            )
            ->where('term_id', school()->activeTerm()?->id) // ✅ PR0: Scoped to Active Term
            ->get()
            ->sortBy(['timeSlot.day_of_week', 'timeSlot.order_index'])
            ->groupBy(fn($t) => $t->timeSlot->day_of_week);

        // Get Attendance Status
        $attendanceStatus = $attendanceService->getWeeklyAttendanceStatus(
            $teacher->id,
            $startOfWeek->format('Y-m-d'),
            $endOfWeek->format('Y-m-d')
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
        ]);
    }
}
