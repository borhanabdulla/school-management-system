<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Timetable\Enums\TimeSlotType;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $template = TimetableTemplate::where('is_default', true)->first();
        
        if (!$template) {
            $this->command->error('Default Timetable Template not found.');
            return;
        }

        // Get all academic slots (lessons)
        $academicSlots = $template->timeSlots()
            ->where('type', TimeSlotType::Academic)
            ->orderBy('day_of_week')
            ->orderBy('order_index')
            ->get();

        $sections = ClassSection::all();

        foreach ($sections as $section) {
            // Get course offerings for this section
            $offerings = CourseOffering::where('class_section_id', $section->id)->get();
            
            if ($offerings->isEmpty()) continue;

            // Simple distribution logic: Cycle through offerings and assign to slots
            $offeringIndex = 0;
            $offeringsCount = $offerings->count();

            foreach ($academicSlots as $slot) {
                $offering = $offerings[$offeringIndex % $offeringsCount];
                
                // Check for teacher conflict (simplified for seeding)
                // In a real scheduler, we'd check Timetable::hasTeacherConflict(...)
                // For seeding, we'll just create it and ignore conflicts or assume random distribution is "good enough" for demo data
                
                Timetable::firstOrCreate(
                    [
                        'class_section_id' => $section->id,
                        'time_slot_id' => $slot->id,
                    ],
                    [
                        'course_offering_id' => $offering->id,
                    ]
                );

                $offeringIndex++;
            }
        }
    }
}
