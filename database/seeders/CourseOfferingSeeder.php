<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;

class CourseOfferingSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear)
            return;

        $sections = ClassSection::with('grade.subjects')->get();
        $teachers = Teacher::all();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers found. Skipping CourseOffering seeding.');
            return;
        }

        foreach ($sections as $section) {
            $gradeSubjects = $section->grade->subjects;

            foreach ($gradeSubjects as $subject) {
                // Assign a random teacher to this subject for this section
                $teacher = $teachers->random();

                CourseOffering::firstOrCreate(
                    [
                        'academic_year_id' => $activeYear->id,
                        'class_section_id' => $section->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'teacher_id' => $teacher->id,
                    ]
                );
            }
        }
    }
}
