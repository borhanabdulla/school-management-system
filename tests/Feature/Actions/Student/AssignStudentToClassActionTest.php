<?php

namespace Tests\Feature\Actions\Student;

use App\Domains\Academic\Student\Actions\AssignStudentToClassAction;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Events\StudentAssignedToClass;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use App\Domains\Academic\ClassSection\Exceptions\ClassroomFullException;
use App\Domains\Academic\Student\Enums\StudentStatus;
use App\Domains\Shared\Enums\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssignStudentToClassActionTest extends TestCase
{
    use RefreshDatabase;

    protected $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        // Create active academic year
        $this->academicYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active'
        ]);
    }


    public function test_it_assigns_student_to_class_successfully()
    {
        Event::fake();

        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create(['name' => 'Grade 1', 'educational_stage_id' => $stage->id, 'level_order' => 1]);

        $section = ClassSection::create([
            'name' => 'Section A',
            'grade_id' => $grade->id,
            'academic_year_id' => $this->academicYear->id,
            'gender_type' => SectionGenderType::Mixed,
            'max_capacity' => 30
        ]);

        $student = Student::create([
            'admission_number' => '20240001',
            'first_name_ar' => 'Ahmed',
            'family_name_ar' => 'Ali',
            'date_of_birth' => '2015-01-01',
            'gender' => Gender::Male,
            'national_id' => '1234567890',
            'status' => StudentStatus::Active
        ]);

        // Create initial enrollment without section
        StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $grade->id,
            'class_section_id' => null,
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
            'status' => 'active'
        ]);

        $action = app(AssignStudentToClassAction::class);
        $enrollment = $action->execute($student, $section);

        $this->assertEquals($section->id, $enrollment->class_section_id);

        Event::assertDispatched(StudentAssignedToClass::class, function ($event) use ($student, $section) {
            return $event->student->id === $student->id && $event->section->id === $section->id;
        });
    }

    public function test_it_fails_if_grade_does_not_match()
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade1 = Grade::create(['name' => 'Grade 1', 'educational_stage_id' => $stage->id, 'level_order' => 1]);
        $grade2 = Grade::create(['name' => 'Grade 2', 'educational_stage_id' => $stage->id, 'level_order' => 2]);

        $section = ClassSection::create([
            'name' => 'Section A',
            'grade_id' => $grade1->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $student = Student::create([
            'admission_number' => '20240002',
            'first_name_ar' => 'Sara',
            'family_name_ar' => 'Ali',
            'date_of_birth' => '2015-01-01',
            'gender' => Gender::Female,
            'national_id' => '1234567891',
            'status' => StudentStatus::Active
        ]);

        // Enrolled in Grade 2
        StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $grade2->id,
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
            'status' => 'active'
        ]);

        $this->expectException(ValidationException::class);

        $action = app(AssignStudentToClassAction::class);
        $action->execute($student, $section);
    }

    public function test_it_fails_if_gender_does_not_match()
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create(['name' => 'Grade 1', 'educational_stage_id' => $stage->id, 'level_order' => 1]);

        $section = ClassSection::create([
            'name' => 'Girls Only',
            'grade_id' => $grade->id,
            'academic_year_id' => $this->academicYear->id,
            'gender_type' => SectionGenderType::Girls // Only females
        ]);

        $student = Student::create([
            'admission_number' => '20240003',
            'first_name_ar' => 'Ahmed',
            'family_name_ar' => 'Ali',
            'date_of_birth' => '2015-01-01',
            'gender' => Gender::Male, // Male student
            'national_id' => '1234567892',
            'status' => StudentStatus::Active
        ]);

        StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $grade->id,
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
            'status' => 'active'
        ]);

        $this->expectException(ValidationException::class);

        $action = app(AssignStudentToClassAction::class);
        $action->execute($student, $section);
    }

    public function test_it_fails_if_class_is_full()
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create(['name' => 'Grade 1', 'educational_stage_id' => $stage->id, 'level_order' => 1]);

        $section = ClassSection::create([
            'name' => 'Full Section',
            'grade_id' => $grade->id,
            'academic_year_id' => $this->academicYear->id,
            'max_capacity' => 1
        ]);

        // Fill the class
        $student1 = Student::create([
            'admission_number' => '20240004',
            'first_name_ar' => 'Student 1',
            'family_name_ar' => 'Test',
            'date_of_birth' => '2015-01-01',
            'gender' => Gender::Male,
            'national_id' => '1234567893',
            'status' => StudentStatus::Active
        ]);

        StudentEnrollment::create([
            'student_id' => $student1->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $grade->id,
            'class_section_id' => $section->id,
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
            'status' => 'active'
        ]);

        $student2 = Student::create([
            'admission_number' => '20240005',
            'first_name_ar' => 'Student 2',
            'family_name_ar' => 'Test',
            'date_of_birth' => '2015-01-01',
            'gender' => Gender::Male,
            'national_id' => '1234567894',
            'status' => StudentStatus::Active
        ]);

        StudentEnrollment::create([
            'student_id' => $student2->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $grade->id,
            'class_section_id' => null,
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
            'status' => 'active'
        ]);

        $this->expectException(ClassroomFullException::class);

        $action = app(AssignStudentToClassAction::class);
        $action->execute($student2, $section);
    }
}
