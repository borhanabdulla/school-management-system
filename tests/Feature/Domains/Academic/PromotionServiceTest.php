<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Promotion\Services\PromotionService;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Student\Enums\StudentStatus;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Shared\Enums\Gender;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_processed_by_and_completes_old_enrollment(): void
    {
        $user = User::create([
            'username' => 'tester',
            'email' => 'tester@example.com',
            'phone' => '0500000001',
            'password' => Hash::make('secret'),
        ]);
        $this->actingAs($user);

        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade1 = Grade::create([
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
            'level_order' => 1,
        ]);
        $grade2 = Grade::create([
            'name' => 'Grade 2',
            'educational_stage_id' => $stage->id,
            'level_order' => 2,
        ]);
        $grade1->update(['next_grade_id' => $grade2->id]);

        $fromYear = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'status' => 'active',
        ]);
        $nextYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'pending',
        ]);

        $student = Student::create([
            'admission_number' => '20239999',
            'first_name_ar' => 'Salem',
            'family_name_ar' => 'Ali',
            'date_of_birth' => '2014-01-01',
            'gender' => Gender::Male,
            'national_id' => '9988776655',
            'status' => StudentStatus::Active,
            'current_grade_id' => $grade1->id,
        ]);

        StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $fromYear->id,
            'grade_id' => $grade1->id,
            'class_section_id' => null,
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
            'status' => 'active',
        ]);

        AnnualResult::create([
            'student_id' => $student->id,
            'academic_year_id' => $fromYear->id,
            'grade_id' => $grade1->id,
            'decision' => 'pass',
        ]);

        $service = app(PromotionService::class);
        $promotion = $service->promote($student, $fromYear, null, $user);

        $this->assertInstanceOf(Promotion::class, $promotion);
        $this->assertSame($user->id, $promotion->processed_by);

        $oldEnrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('academic_year_id', $fromYear->id)
            ->firstOrFail();
        $this->assertEquals(EnrollmentStatus::Completed, $oldEnrollment->status);
    }
}
