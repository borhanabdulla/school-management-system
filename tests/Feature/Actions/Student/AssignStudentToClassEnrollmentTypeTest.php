<?php

namespace Tests\Feature\Actions\Student;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Student\Actions\AssignStudentToClassAction;
use App\Domains\Academic\Student\Enums\EnrollmentType;
use App\Domains\Academic\Student\Enums\StudentStatus;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Shared\Enums\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignStudentToClassEnrollmentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_enrollment_with_type_new_when_missing(): void
    {
        $year = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active',
        ]);

        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create([
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
            'level_order' => 1,
        ]);

        $section = ClassSection::create([
            'name' => 'A',
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'gender_type' => SectionGenderType::Mixed,
            'max_capacity' => 30,
        ]);

        $student = Student::create([
            'admission_number' => '20240010',
            'first_name_ar' => 'Omar',
            'family_name_ar' => 'Hassan',
            'date_of_birth' => '2015-01-01',
            'gender' => Gender::Male,
            'national_id' => '1234567899',
            'status' => StudentStatus::Active,
            'current_grade_id' => $grade->id,
        ]);

        $action = app(AssignStudentToClassAction::class);
        $enrollment = $action->execute($student, $section);

        $this->assertSame($section->id, $enrollment->class_section_id);
        $this->assertSame(EnrollmentType::New, $enrollment->enrollment_type);
        $this->assertSame($year->id, $enrollment->academic_year_id);
        $this->assertSame($student->id, $enrollment->student_id);
    }
}
