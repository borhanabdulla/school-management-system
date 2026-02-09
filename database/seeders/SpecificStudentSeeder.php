<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Actions\RegisterStudentAction;
use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Shared\Enums\Gender;
use Faker\Factory as Faker;

class SpecificStudentSeeder extends Seeder
{
    public function run(RegisterStudentAction $registerAction)
    {
        $faker = Faker::create('ar_SA');

        // 1. Fetch Grades
        $grade1 = Grade::where('name', 'like', '%اول%')->firstOrFail();
        $grade2 = Grade::where('name', 'like', '%ثاني%')->firstOrFail();

        // 2. Fetch Sections for Grade 1
        $grade1Sections = ClassSection::where('grade_id', $grade1->id)->get();
        if ($grade1Sections->count() < 2) {
            $this->command->error("Grade 1 needs at least 2 sections.");
            return;
        }
        $grade1SectionA = $grade1Sections[0];
        $grade1SectionB = $grade1Sections[1];

        // 3. Fetch Sections for Grade 2
        $grade2Sections = ClassSection::where('grade_id', $grade2->id)->get();
        if ($grade2Sections->count() < 2) {
            $this->command->error("Grade 2 needs at least 2 sections.");
            return;
        }
        $grade2SectionA = $grade2Sections[0];
        $grade2SectionB = $grade2Sections[1];

        // 4. Define Students
        $students = [
            // Grade 1 - Section A (5 Students)
            ['grade' => $grade1, 'section' => $grade1SectionA, 'name' => 'أحمد محمد علي'],
            ['grade' => $grade1, 'section' => $grade1SectionA, 'name' => 'خالد عبدالله عمر'],
            ['grade' => $grade1, 'section' => $grade1SectionA, 'name' => 'سارة يوسف حسن', 'gender' => Gender::Female],
            ['grade' => $grade1, 'section' => $grade1SectionA, 'name' => 'نورة فهد سعود', 'gender' => Gender::Female],
            ['grade' => $grade1, 'section' => $grade1SectionA, 'name' => 'عمر إبراهيم خليل'],

            // Grade 1 - Section B (5 Students)
            ['grade' => $grade1, 'section' => $grade1SectionB, 'name' => 'ياسر كمال الدين'],
            ['grade' => $grade1, 'section' => $grade1SectionB, 'name' => 'منى سعيد صالح', 'gender' => Gender::Female],
            ['grade' => $grade1, 'section' => $grade1SectionB, 'name' => 'هند عبدالكريم'],
            ['grade' => $grade1, 'section' => $grade1SectionB, 'name' => 'فيصل عبدالعزيز'],
            ['grade' => $grade1, 'section' => $grade1SectionB, 'name' => 'تركي ناصر'],

            // Grade 2 - Section A (5 Students)
            ['grade' => $grade2, 'section' => $grade2SectionA, 'name' => 'ماجد عبدالله'],
            ['grade' => $grade2, 'section' => $grade2SectionA, 'name' => 'سلطان محمد'],
            ['grade' => $grade2, 'section' => $grade2SectionA, 'name' => 'ليلى أحمد', 'gender' => Gender::Female],
            ['grade' => $grade2, 'section' => $grade2SectionA, 'name' => 'ريم خالد', 'gender' => Gender::Female],
            ['grade' => $grade2, 'section' => $grade2SectionA, 'name' => 'عبدالرحمن يوسف'],

            // Grade 2 - Section B (5 Students)
            ['grade' => $grade2, 'section' => $grade2SectionB, 'name' => 'زياد فهد'],
            ['grade' => $grade2, 'section' => $grade2SectionB, 'name' => 'مشعل سعود'],
            ['grade' => $grade2, 'section' => $grade2SectionB, 'name' => 'حنان إبراهيم', 'gender' => Gender::Female],
            ['grade' => $grade2, 'section' => $grade2SectionB, 'name' => 'أمل سعيد', 'gender' => Gender::Female],
            ['grade' => $grade2, 'section' => $grade2SectionB, 'name' => 'بدر ناصر'],
        ];

        foreach ($students as $studentData) {
            $names = explode(' ', $studentData['name'], 2);
            $firstName = $names[0];
            $lastName = $names[1] ?? 'العائلة';

            $data = new StudentRegistrationData(
                student: [
                    'first_name_ar' => $firstName,
                    'family_name_ar' => $lastName,
                    'date_of_birth' => '2016-01-01', // Example DOB
                    'gender' => $studentData['gender'] ?? Gender::Male,
                    'nationality_id' => 1, // Saudi
                    'blood_type' => 'O+',
                    'national_id' => $faker->unique()->numerify('1#########'),
                ],
                guardians: [
                    [
                        'data' => [
                            'first_name' => 'ولي أمر',
                            'last_name' => $lastName,
                            'national_id' => $faker->unique()->numerify('1#########'),
                            'phone' => $faker->unique()->numerify('05########'),
                        ],
                        'relationship' => 'father',
                        'is_financial_sponsor' => true,
                        'is_emergency_contact' => true,
                    ]
                ],
                grade_id: $studentData['grade']->id,
                class_section_id: $studentData['section']->id,
                health_data: [],
                address: [
                    'city' => 'الرياض',
                    'district' => 'الملز',
                    'street_name' => 'شارع الجامعة',
                ],
                documents: [],
                is_transfer: false,
                previous_history: null,
                create_invoice: false // Skip invoice creation
            );

            try {
                $registerAction->execute($data);
                $this->command->info("Registered: {$studentData['name']} in {$studentData['grade']->name} - {$studentData['section']->name}");
            } catch (\Exception $e) {
                $this->command->error("Failed to register {$studentData['name']}: " . $e->getMessage());
            }
        }
    }
}
