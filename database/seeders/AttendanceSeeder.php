<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Shared\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🗓️ Seeding Attendance Records...');

        // Get a teacher/admin user to record attendance
        $recorder = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['teacher', 'admin', 'super_admin']);
        })->first();

        if (!$recorder) {
            $recorder = User::first();
        }

        // Get all sections with their students
        $sections = ClassSection::with([
            'students' => function ($q) {
                $q->where('status', 'active');
            }
        ])->get();

        // Get timetables for generating attendance per session
        $timetables = Timetable::with(['timeSlot', 'classSection'])->get()
            ->groupBy('class_section_id');

        // Generate attendance for the last 30 days
        $today = Carbon::now();
        $startDate = $today->copy()->subDays(30);

        $totalRecords = 0;

        foreach ($sections as $section) {
            $students = $section->students;
            if ($students->isEmpty())
                continue;

            $sectionTimetables = $timetables->get($section->id, collect());
            if ($sectionTimetables->isEmpty())
                continue;

            // Loop through each day
            $currentDate = $startDate->copy();
            while ($currentDate->lte($today)) {
                // Skip weekends (Friday & Saturday in Saudi Arabia)
                if (!in_array($currentDate->dayOfWeek, [5, 6])) { // 5=Friday, 6=Saturday

                    // Get timetables for this day of week
                    $dayTimetables = $sectionTimetables->filter(function ($tt) use ($currentDate) {
                        return $tt->timeSlot && $tt->timeSlot->day_of_week == $currentDate->dayOfWeekIso;
                    });

                    foreach ($dayTimetables as $timetable) {
                        foreach ($students as $student) {
                            // Realistic distribution: 85% present, 10% absent, 5% late
                            $rand = rand(1, 100);
                            if ($rand <= 85) {
                                $status = AttendanceStatus::PRESENT->value;
                                $delayMinutes = 0;
                                $remarks = null;
                            } elseif ($rand <= 95) {
                                $status = AttendanceStatus::ABSENT->value;
                                $delayMinutes = 0;
                                $remarks = $this->getRandomAbsenceReason();
                            } else {
                                $status = AttendanceStatus::LATE->value;
                                $delayMinutes = rand(5, 25);
                                $remarks = null;
                            }

                            Attendance::firstOrCreate(
                                [
                                    'student_id' => $student->id,
                                    'date' => $currentDate->format('Y-m-d'),
                                    'time_slot_id' => $timetable->time_slot_id,
                                ],
                                [
                                    'class_section_id' => $section->id,
                                    'status' => $status,
                                    'remarks' => $remarks,
                                    'delay_minutes' => $delayMinutes,
                                    'recorded_by' => $recorder?->id,
                                ]
                            );
                            $totalRecords++;
                        }
                    }
                }
                $currentDate->addDay();
            }
        }

        $this->command->info("✅ Created {$totalRecords} attendance records.");
    }

    private function getRandomAbsenceReason(): string
    {
        $reasons = [
            'مرض',
            'ظرف عائلي',
            'موعد طبي',
            'إجازة رسمية',
            null,
        ];
        return $reasons[array_rand($reasons)] ?? '';
    }
}
