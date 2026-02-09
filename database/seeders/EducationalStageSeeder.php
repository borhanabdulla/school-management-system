<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;

class EducationalStageSeeder extends Seeder
{
    public function run(): void
    {
        // Get the active academic year
        $activeYear = AcademicYear::where('status', 'active')->first();

        if (!$activeYear) {
            $this->command->error('No active academic year found. Please run AcademicYearSeeder first.');
            return;
        }

        // Create Elementary Stage
        $elementary = EducationalStage::firstOrCreate(
            ['name' => 'المرحلة الابتدائية'],
            [
                'rank' => 1,
                'min_passing_percentage' => 50.00,
                'grading_system' => 'standard',
            ]
        );

        // Create Grades for Elementary Stage
        $grades = [
            ['name' => 'الأول الابتدائي', 'order' => 1],
            ['name' => 'الثاني الابتدائي', 'order' => 2],
            ['name' => 'الثالث الابتدائي', 'order' => 3],
        ];

        foreach ($grades as $gradeData) {
            $grade = Grade::firstOrCreate(
                [
                    'educational_stage_id' => $elementary->id,
                    'name' => $gradeData['name'],
                ],
                [
                    'level_order' => $gradeData['order'],
                    'min_age' => 5 + $gradeData['order'],
                    'max_age' => 7 + $gradeData['order'],
                ]
            );

            // Create 3 Sections for each Grade
            $sections = ['أ', 'ب', 'ج'];
            foreach ($sections as $sectionName) {
                ClassSection::firstOrCreate(
                    [
                        'grade_id' => $grade->id,
                        'academic_year_id' => $activeYear->id,
                        'name' => $sectionName,
                    ],
                    [
                        'max_capacity' => 30,
                        'gender_type' => \App\Domains\Academic\ClassSection\Enums\SectionGenderType::Mixed,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
