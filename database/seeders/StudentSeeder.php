<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Shared\Models\User;
use Faker\Factory;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $fakerAr = Factory::create('ar_SA');
        $fakerEn = Factory::create('en_US');

        // Get all sections
        $sections = ClassSection::all();

        foreach ($sections as $section) {
            // Create 5 students for each section
            for ($i = 1; $i <= 5; $i++) {
                // Generate a somewhat deterministic admission number based on section and index to avoid collisions on re-runs
                // Format: GradeID + SectionID + Index (padded)
                $admissionNumber = sprintf('%d%d%03d', $section->grade_id, $section->id, $i);
                $email = "student_{$admissionNumber}@school.com";

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'username' => "student_{$admissionNumber}",
                        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                        'is_active' => true,
                    ]
                );

                Student::firstOrCreate(
                    ['admission_number' => $admissionNumber],
                    [
                        'user_id' => $user->id,
                        'first_name_ar' => $fakerAr->firstName,
                        'family_name_ar' => $fakerAr->lastName,
                        'first_name_en' => $fakerEn->firstName,
                        'family_name_en' => $fakerEn->lastName,
                        'date_of_birth' => $fakerEn->dateTimeBetween('-12 years', '-6 years'),
                        'gender' => $fakerEn->randomElement(['male', 'female']),
                        'national_id' => $fakerEn->unique()->numerify('##########'),
                        'current_grade_id' => $section->grade_id,
                        'current_class_section_id' => $section->id,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
