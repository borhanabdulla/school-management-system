<?php

namespace Tests\Feature\Actions\Student;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Actions\RegisterStudentAction;
use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Academic\Student\Enums\GuardianRelationship;
use App\Domains\Academic\Student\Exceptions\DuplicateStudentException;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterStudentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_student_with_enrollment_and_placement(): void
    {
        $year = AcademicYear::factory()->active()->create();
        school()->invalidateYear();
        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $data = new StudentRegistrationData(
            student: [
                'first_name_ar' => 'Ahmed',
                'family_name_ar' => 'Ali',
                'date_of_birth' => '2015-01-01',
                'gender' => 'male',
                'nationality_id' => null,
                'blood_type' => null,
                'national_id' => '1010101010',
            ],
            guardians: [
                [
                    'data' => [
                        'first_name' => 'Parent',
                        'last_name' => 'One',
                        'national_id' => 'G-100',
                        'phone' => '0500000001',
                        'nationality_id' => null,
                    ],
                    'relationship' => GuardianRelationship::Father->value,
                    'is_financial_sponsor' => true,
                    'is_emergency_contact' => true,
                    'lives_with' => true,
                    'has_portal_access' => true,
                ],
            ],
            grade_id: $grade->id,
            class_section_id: $section->id,
            health_data: [],
            address: [],
            documents: [],
            photo: null,
            is_transfer: false,
            previous_history: null,
            create_invoice: false
        );

        $action = app(RegisterStudentAction::class);
        $student = $action->execute($data);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertFalse(str_starts_with($student->admission_number, 'TMP-'));
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);

        $student->refresh();
        $this->assertSame($grade->id, $student->current_grade_id);
        $this->assertSame($section->id, $student->current_class_section_id);
        $this->assertCount(1, $student->guardians);
    }

    public function test_it_does_not_duplicate_guardians(): void
    {
        $year = AcademicYear::factory()->active()->create();
        school()->invalidateYear();
        $grade = Grade::factory()->create();

        $guardianData = [
            'data' => [
                'first_name' => 'Parent',
                'last_name' => 'Two',
                'national_id' => 'G-200',
                'phone' => '0500000002',
                'nationality_id' => null,
            ],
            'relationship' => GuardianRelationship::Father->value,
            'is_financial_sponsor' => true,
            'is_emergency_contact' => true,
            'lives_with' => true,
            'has_portal_access' => true,
        ];

        $data = new StudentRegistrationData(
            student: [
                'first_name_ar' => 'Sara',
                'family_name_ar' => 'Ali',
                'date_of_birth' => '2015-02-01',
                'gender' => 'female',
                'nationality_id' => null,
                'blood_type' => null,
                'national_id' => '1010102020',
            ],
            guardians: [$guardianData, $guardianData],
            grade_id: $grade->id,
            class_section_id: null,
            health_data: [],
            address: [],
            documents: [],
            photo: null,
            is_transfer: false,
            previous_history: null,
            create_invoice: false
        );

        $action = app(RegisterStudentAction::class);
        $student = $action->execute($data);

        $this->assertCount(1, $student->guardians()->get());
        $this->assertDatabaseCount('student_guardian', 1);
    }

    public function test_it_rejects_duplicate_national_id(): void
    {
        AcademicYear::factory()->active()->create();
        school()->invalidateYear();
        $grade = Grade::factory()->create();

        Student::factory()->create([
            'national_id' => '1010103030',
        ]);

        $data = new StudentRegistrationData(
            student: [
                'first_name_ar' => 'Khaled',
                'family_name_ar' => 'Ali',
                'date_of_birth' => '2014-01-01',
                'gender' => 'male',
                'nationality_id' => null,
                'blood_type' => null,
                'national_id' => '1010103030',
            ],
            guardians: [
                [
                    'data' => [
                        'first_name' => 'Parent',
                        'last_name' => 'Three',
                        'national_id' => 'G-300',
                        'phone' => '0500000003',
                        'nationality_id' => null,
                    ],
                    'relationship' => GuardianRelationship::Father->value,
                ],
            ],
            grade_id: $grade->id,
            class_section_id: null,
            health_data: [],
            address: [],
            documents: [],
            photo: null,
            is_transfer: false,
            previous_history: null,
            create_invoice: false
        );

        $this->expectException(DuplicateStudentException::class);

        $action = app(RegisterStudentAction::class);
        $action->execute($data);
    }
}
