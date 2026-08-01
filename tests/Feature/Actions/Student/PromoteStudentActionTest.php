<?php

namespace Tests\Feature\Actions\Student;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Student\Actions\PromoteStudentAction;
use App\Domains\Academic\Student\Enums\EnrollmentType;
use App\Domains\Academic\Student\Enums\StudentStatus;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Shared\Enums\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoteStudentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_enrollment_type_on_promotion(): void
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $currentGrade = Grade::create([
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
            'level_order' => 1,
        ]);
        $nextGrade = Grade::create([
            'name' => 'Grade 2',
            'educational_stage_id' => $stage->id,
            'level_order' => 2,
        ]);

        $nextYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'pending',
        ]);

        $student = Student::create([
            'admission_number' => '20240001',
            'first_name_ar' => 'Ahmed',
            'family_name_ar' => 'Ali',
            'date_of_birth' => '2015-01-01',
            'gender' => Gender::Male,
            'national_id' => '1234567890',
            'status' => StudentStatus::Active,
            'current_grade_id' => $currentGrade->id,
        ]);

        $action = app(PromoteStudentAction::class);
        $enrollment = $action->execute($student, $nextYear, $nextGrade);

        $this->assertSame(EnrollmentType::Returning, $enrollment->enrollment_type);
        $this->assertSame($nextYear->id, $enrollment->academic_year_id);
        $this->assertSame($nextGrade->id, $enrollment->grade_id);
        $this->assertSame($student->id, $enrollment->student_id);
    }
}
