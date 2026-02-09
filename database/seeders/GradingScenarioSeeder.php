<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\HR\Teacher\Actions\CreateTeacherAction;
use App\Domains\HR\Teacher\Data\TeacherOnboardingData;
use App\Domains\Academic\CourseOffering\Actions\CreateCourseOfferingAction;
use App\Domains\Academic\CourseOffering\Data\CourseOfferingData;
use App\Domains\Academic\Grading\Actions\CreateGradingTemplateAction;
use App\Domains\Academic\Student\Actions\RegisterStudentAction;
use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Shared\Enums\Gender;
use App\Domains\Shared\Models\Country;

class GradingScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🧹 Cleaning database...');

            $this->command->info('🏗️ Building Academic Structure...');

            // 0. Country
            $country = Country::create([
                'name_ar' => 'المملكة العربية السعودية',
                'name_en' => 'Saudi Arabia',
                'code' => 'SA',
                'phone_code' => '966',
            ]);

            // 1. Academic Year
            $academicYear = AcademicYear::create([
                'name' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'status' => AcademicYearStatus::Active,
            ]);

            // 2. Term
            $term = Term::create([
                'academic_year_id' => $academicYear->id,
                'name' => 'الفصل الأول',
                'start_date' => '2024-09-01',
                'end_date' => '2025-01-30',
                'status' => 'active',
            ]);

            // 3. Grade
            $grade = Grade::factory()->create([
                'name' => 'الصف الأول',
            ]);

            // 4. Class Sections (A and B)
            $sectionA = ClassSection::create([
                'grade_id' => $grade->id,
                'academic_year_id' => $academicYear->id,
                'name' => 'أ',
            ]);

            $sectionB = ClassSection::create([
                'grade_id' => $grade->id,
                'academic_year_id' => $academicYear->id,
                'name' => 'ب',
            ]);

            // 5. Subject
            $subject = Subject::create([
                'name' => 'الرياضيات',
                'code' => 'MATH101',
            ]);

            // 6. Teacher (Using Action)
            $this->command->info('👨‍🏫 Creating Teacher via Action...');
            $createTeacherAction = app(CreateTeacherAction::class);
            $teacherData = new TeacherOnboardingData(
                first_name: 'Teacher',
                last_name: 'One',
                email: 'teacher1@school.com',
                password: 'password',
                specialization: 'Math',
                max_weekly_classes: 20,
                hire_date: now()->format('Y-m-d'),
                phone: '0500000001'
            );
            $teacher = $createTeacherAction->execute($teacherData);

            // 7. Course Offering (Using Action)
            $this->command->info('📚 Creating Course Offering via Action...');
            $createCourseOfferingAction = app(CreateCourseOfferingAction::class);

            // Course for Section A
            $courseDataA = new CourseOfferingData(
                academic_year_id: $academicYear->id,
                term_id: $term->id,
                class_section_id: $sectionA->id,
                subject_id: $subject->id,
                teacher_id: $teacher->id
            );
            $courseOfferingA = $createCourseOfferingAction->execute($courseDataA);

            // Course for Section B
            $courseDataB = new CourseOfferingData(
                academic_year_id: $academicYear->id,
                term_id: $term->id,
                class_section_id: $sectionB->id,
                subject_id: $subject->id,
                teacher_id: $teacher->id
            );
            $courseOfferingB = $createCourseOfferingAction->execute($courseDataB);


            // 8. Grading Template (Using Action)
            $this->command->info('📝 Creating Grading Template via Action...');
            $createTemplateAction = app(CreateGradingTemplateAction::class);
            $templateData = [
                'name' => 'قالب التقييم الأساسي',
                'total_max_score' => 100,
                'pass_score' => 50,
                'academic_year_id' => $academicYear->id,
            ];
            $categoriesData = [
                [
                    'name' => 'أعمال السنة',
                    'weight' => 40,
                    'children' => [
                        ['name' => 'تحريري', 'weight' => 60],
                        ['name' => 'شفهي', 'weight' => 40],
                    ]
                ],
                [
                    'name' => 'اختبار نهائي',
                    'weight' => 60,
                ]
            ];
            $template = $createTemplateAction->execute($templateData, $categoriesData);

            // Retrieve categories for assessment creation
            $writtenCategory = TemplateCategory::where('name', 'تحريري')->where('grading_template_id', $template->id)->first();
            $oralCategory = TemplateCategory::where('name', 'شفهي')->where('grading_template_id', $template->id)->first();
            $finalCategory = TemplateCategory::where('name', 'اختبار نهائي')->where('grading_template_id', $template->id)->first();


            // 9. Register Students (Using Action)
            $this->command->info('👨‍🎓 Registering 20 Students via Action...');
            $registerStudentAction = app(RegisterStudentAction::class);

            $students = [];

            // Register 10 students for Section A
            for ($i = 1; $i <= 10; $i++) {
                $students[] = $this->registerStudent($registerStudentAction, $grade->id, $sectionA->id, $i, 'A', $country->id);
            }

            // Register 10 students for Section B
            for ($i = 1; $i <= 10; $i++) {
                $students[] = $this->registerStudent($registerStudentAction, $grade->id, $sectionB->id, $i, 'B', $country->id);
            }

            // 10. Assessments & Marks (Manual for now as no specific action found)
            $this->command->info('📄 Creating Assessments and Marks...');

            // Create Assessments for both courses
            foreach ([$courseOfferingA, $courseOfferingB] as $course) {
                $writtenAssessment = Assessment::create([
                    'course_offering_id' => $course->id,
                    'template_category_id' => $writtenCategory->id,
                    'title' => 'اختبار تحريري شهري',
                    'max_score' => 100,
                    'is_published' => true,
                ]);

                $oralAssessment = Assessment::create([
                    'course_offering_id' => $course->id,
                    'template_category_id' => $oralCategory->id,
                    'title' => 'اختبار شفهي',
                    'max_score' => 100,
                    'is_published' => true,
                ]);

                $finalAssessment = Assessment::create([
                    'course_offering_id' => $course->id,
                    'template_category_id' => $finalCategory->id,
                    'title' => 'الاختبار النهائي',
                    'max_score' => 100,
                    'is_published' => true,
                ]);

                // Assign random marks for students in this section
                foreach ($students as $student) {
                    if ($student->current_class_section_id == $course->class_section_id) {
                        StudentMark::create(['student_id' => $student->id, 'assessment_id' => $writtenAssessment->id, 'raw_score' => rand(50, 100)]);
                        StudentMark::create(['student_id' => $student->id, 'assessment_id' => $oralAssessment->id, 'raw_score' => rand(50, 100)]);
                        StudentMark::create(['student_id' => $student->id, 'assessment_id' => $finalAssessment->id, 'raw_score' => rand(50, 100)]);
                    }
                }
            }

            $this->command->info('✅ Grading Scenario Seeded Successfully with Domain Actions!');
        });
    }

    private function registerStudent(RegisterStudentAction $action, int $gradeId, int $sectionId, int $index, string $sectionName, int $countryId)
    {
        $faker = \Faker\Factory::create('ar_SA');
        $gender = $index % 2 == 0 ? Gender::Male : Gender::Female;

        $studentData = new StudentRegistrationData(
            student: [
                'first_name_ar' => $faker->firstName($gender->value),
                'family_name_ar' => $faker->lastName,
                'date_of_birth' => '2015-01-01',
                'gender' => $gender->value,
                'nationality_id' => $countryId,
                'blood_type' => 'O+',
                'national_id' => '10000000' . $sectionName . str_pad($index, 2, '0', STR_PAD_LEFT),
            ],
            guardians: [
                [
                    'data' => [
                        'first_name' => $faker->firstNameMale,
                        'last_name' => $faker->lastName,
                        'phone' => '05' . $sectionName . str_pad($index, 7, '0', STR_PAD_LEFT), // Unique phone
                        'national_id' => '20000000' . $sectionName . str_pad($index, 2, '0', STR_PAD_LEFT),
                        'nationality_id' => $countryId,
                    ],
                    'relationship' => 'father',
                    'is_financial_sponsor' => true,
                    'is_emergency_contact' => true,
                ]
            ],
            grade_id: $gradeId,
            class_section_id: $sectionId,
            health_data: [],
            address: [
                'city' => 'Riyadh',
                'district' => 'Olaya',
                'street_name' => 'Main St',
            ],
            documents: [],
            is_transfer: false,
            previous_history: null,
            create_invoice: false // Skip invoice for seeding speed/complexity
        );

        return $action->execute($studentData);
    }
}
