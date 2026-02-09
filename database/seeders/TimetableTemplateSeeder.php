<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;

class TimetableTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        if (!$activeYear) {
            $this->command->error('No active academic year found. Please run AcademicYearSeeder first.');
            return;
        }

        // Create Default Template
        $template = TimetableTemplate::firstOrCreate(
            ['name' => 'الدوام الافتراضي', 'academic_year_id' => $activeYear->id],
            [
                'description' => 'الجدول الافتراضي للحصص الدراسية',
                'working_days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'],
                'is_default' => true,
                'status' => TemplateStatus::Active,
            ]
        );

        // Define Slots Structure
        // 1: Inspection, 2: Lesson, 3: Lesson, Break, 4: Inspection, 5: Lesson, 6: Lesson, 7: Lesson
        $slots = [
            ['order' => 1, 'name' => 'تفتيش صباحي', 'type' => 'inspection', 'duration' => 15],
            ['order' => 2, 'name' => 'الحصة الأولى', 'type' => 'academic', 'duration' => 45],
            ['order' => 3, 'name' => 'الحصة الثانية', 'type' => 'academic', 'duration' => 45],
            ['order' => 4, 'name' => 'الحصة الثالثة', 'type' => 'academic', 'duration' => 45],
            ['order' => 5, 'name' => 'فسحة', 'type' => 'break', 'duration' => 30],
            ['order' => 6, 'name' => 'تفتيش', 'type' => 'inspection', 'duration' => 15], // Requested as 4th "event" but logically after break? User said "Inspection in 1st and 4th". 
            // Let's interpret "4th" as the 4th *period* slot, or the 4th event?
            // User said: "Make break only after the third [period]... and make inspection in the first and fourth".
            // Usually "First" means 1st period. "Fourth" means 4th period.
            // So: 1(Insp), 2(L), 3(L), 4(L), Break, 5(Insp), 6(L)... ? 
            // OR: 1(Insp), 2(L), 3(L), Break, 4(Insp), 5(L)...
            // Let's follow the user's specific instruction: "Break after the third" and "Inspection in the first and fourth".
            // Sequence:
            // 1. Inspection (First)
            // 2. Lesson 1
            // 3. Lesson 2
            // 4. Lesson 3
            // -- Break -- (After 3rd)
            // 5. Inspection (Fourth? Or is this the 4th lesson slot?)
            // If "Inspection in the first and fourth" refers to the *order of events*:
            // 1. Inspection
            // 2. Lesson
            // 3. Lesson
            // 4. Inspection (This would be before break if break is after 3rd?)

            // Let's assume standard school structure where "First" and "Fourth" refer to the *Lesson Number* usually, but here user explicitly asked for Inspection.
            // Let's try to map it as:
            // Slot 1: Inspection (The "First")
            // Slot 2: Lesson 1
            // Slot 3: Lesson 2
            // Slot 4: Lesson 3
            // Slot 5: Break (After 3rd Lesson)
            // Slot 6: Inspection (The "Fourth" event? Or Inspection *instead* of 4th lesson?)
            // Let's assume Inspection is an event *between* lessons or *as* a slot.

            // Re-reading user: "Make break only after the third [period]... and make inspection in the first and fourth".
            // Interpretation A: Inspection is Slot 1. Lesson is Slot 2, 3, 4. Break is Slot 5. Inspection is Slot 6. Lesson is Slot 7...
            // Interpretation B: Inspection IS the 1st Period. Inspection IS the 4th Period.
            // Slot 1: Inspection
            // Slot 2: Lesson
            // Slot 3: Lesson
            // -- Break -- (After 3rd slot?)
            // Slot 4: Inspection

            // Let's go with Interpretation A modified for logical flow:
            // 1. Inspection (Morning Assembly/Inspection)
            // 2. Lesson 1
            // 3. Lesson 2
            // 4. Lesson 3
            // -- Break --
            // 5. Inspection (Post-break check?)
            // 6. Lesson 4
            // 7. Lesson 5

            // Let's stick to the plan I wrote in the implementation plan which user approved:
            // Period 1: Inspection (تفتيش)
            // Period 2: Lesson
            // Period 3: Lesson
            // Break (فسحة)
            // Period 4: Inspection (تفتيش)
            // Period 5: Lesson
            // ...

            // Wait, if Period 1 is Inspection, then we have:
            // 1. Inspection
            // 2. Lesson
            // 3. Lesson
            // -- Break -- (After 3rd "thing"?)
            // 4. Inspection

            // Let's implement exactly this sequence.
        ];

        // Re-defining slots based on "Inspection in 1st and 4th" and "Break after 3rd".
        // 1. Inspection
        // 2. Lesson
        // 3. Lesson
        // -- Break --
        // 4. Inspection
        // 5. Lesson
        // 6. Lesson
        // 7. Lesson

        $startTime = '07:00:00';

        $slotsData = [
            ['name' => 'تفتيش صباحي', 'type' => 'inspection', 'duration' => 15], // 1
            ['name' => 'الحصة الأولى', 'type' => 'academic', 'duration' => 45],   // 2
            ['name' => 'الحصة الثانية', 'type' => 'academic', 'duration' => 45],   // 3
            ['name' => 'فسحة', 'type' => 'break', 'duration' => 30],              // Break after 3rd item
            ['name' => 'تفتيش', 'type' => 'inspection', 'duration' => 15],        // 4 (Technically 5th slot, but 4th "activity" block?)
            // User said "Inspection in the first and fourth". If we count Break as a slot, Inspection is 5th. 
            // If we count "Periods", Inspection is 1st Period. 4th Period is Inspection.
            // Let's assume "Period" = "Hissa" or "Slot".
            // 1. Inspection
            // 2. Lesson
            // 3. Lesson
            // 4. Inspection (This satisfies "Inspection in 4th")
            // But user said "Break after 3rd".
            // So: 1, 2, 3, Break, 4.
            // This fits perfectly.

            ['name' => 'الحصة الثالثة', 'type' => 'academic', 'duration' => 45],   // 5
            ['name' => 'الحصة الرابعة', 'type' => 'academic', 'duration' => 45],   // 6
            ['name' => 'الحصة الخامسة', 'type' => 'academic', 'duration' => 45],   // 7
        ];

        // Let's adjust the names to be more standard "Lesson 1", "Lesson 2" etc if they are academic.
        // But if Inspection takes the slot of "Lesson 1", then the next academic lesson is technically "Lesson 1" academically but "Slot 2".
        // I will name them generically "Hissa" or specific names.

        $finalSlots = [
            ['order' => 1, 'name' => 'تفتيش صباحي', 'type' => 'activity', 'duration' => 15],
            ['order' => 2, 'name' => 'الحصة الأولى', 'type' => 'academic', 'duration' => 45],
            ['order' => 3, 'name' => 'الحصة الثانية', 'type' => 'academic', 'duration' => 45],
            ['order' => 4, 'name' => 'فسحة', 'type' => 'break', 'duration' => 30],
            ['order' => 5, 'name' => 'تفتيش', 'type' => 'activity', 'duration' => 15],
            ['order' => 6, 'name' => 'الحصة الثالثة', 'type' => 'academic', 'duration' => 45],
            ['order' => 7, 'name' => 'الحصة الرابعة', 'type' => 'academic', 'duration' => 45],
            ['order' => 8, 'name' => 'الحصة الخامسة', 'type' => 'academic', 'duration' => 45],
        ];

        $dayMap = [
            'sunday' => \App\Domains\Shared\Enums\DayOfWeek::Sunday->value,
            'monday' => \App\Domains\Shared\Enums\DayOfWeek::Monday->value,
            'tuesday' => \App\Domains\Shared\Enums\DayOfWeek::Tuesday->value,
            'wednesday' => \App\Domains\Shared\Enums\DayOfWeek::Wednesday->value,
            'thursday' => \App\Domains\Shared\Enums\DayOfWeek::Thursday->value,
            'friday' => \App\Domains\Shared\Enums\DayOfWeek::Friday->value,
            'saturday' => \App\Domains\Shared\Enums\DayOfWeek::Saturday->value,
        ];

        foreach ($template->working_days as $day) {
            $dayValue = $dayMap[strtolower($day)] ?? null;
            if ($dayValue === null)
                continue;

            $currentTime = \Carbon\Carbon::parse($startTime);

            foreach ($finalSlots as $slot) {
                TimeSlot::firstOrCreate(
                    [
                        'template_id' => $template->id,
                        'day_of_week' => $dayValue,
                        'order_index' => $slot['order'],
                    ],
                    [
                        'label' => $slot['name'],
                        'type' => $slot['type'],
                        'start_time' => $currentTime->format('H:i:s'),
                        'end_time' => $currentTime->addMinutes($slot['duration'])->format('H:i:s'),
                        'is_attendance_checkpoint' => $slot['type'] === 'activity', // Assume inspections/activities are checkpoints
                    ]
                );
            }
        }
    }
}
