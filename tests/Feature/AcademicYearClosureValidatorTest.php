<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Validation\AcademicYearClosureValidator;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Student\Enums\StudentStatus;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Shared\Models\User;
use Database\Factories\Domains\Academic\Student\Models\StudentFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearClosureValidatorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_closing_when_all_terms_completed_and_active_students_promoted()
    {
        $year = AcademicYear::factory()->create();

        Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Completed,
        ]);

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);

        $activeStudent = StudentFactory::new()->create([
            'status' => StudentStatus::Active->value,
            'current_class_section_id' => $section->id,
            'current_grade_id' => $grade->id,
        ]);

        // ✅ إنشاء StudentEnrollment للطالب (مطلوب للـ Validator الجديد)
        StudentEnrollment::factory()->create([
            'student_id' => $activeStudent->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'class_section_id' => $section->id,
            'status' => EnrollmentStatus::Active,
        ]);

        $inactiveStudent = StudentFactory::new()->create([
            'status' => StudentStatus::Suspended->value,
            'current_class_section_id' => $section->id,
            'current_grade_id' => $grade->id,
        ]);

        AnnualResult::create([
            'student_id' => $activeStudent->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'decision' => 'pass',
        ]);

        Promotion::create([
            'student_id' => $activeStudent->id,
            'academic_year_id' => $year->id,
            'type' => 'promoted',
            'is_reverted' => false,
            'from_grade_id' => $grade->id,
            'processed_by' => User::factory()->create()->id,
            'processed_at' => now(),
        ]);

        $result = app(AcademicYearClosureValidator::class)->validate($year);

        $this->assertTrue($result['can']);
        $this->assertCount(0, $result['issues']);
    }

    /** @test */
    public function it_blocks_closing_when_active_students_are_not_promoted()
    {
        $year = AcademicYear::factory()->create();

        Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Completed,
        ]);

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);

        $activeStudent = StudentFactory::new()->create([
            'status' => StudentStatus::Active->value,
            'current_class_section_id' => $section->id,
            'current_grade_id' => $grade->id,
        ]);

        // ✅ إنشاء StudentEnrollment للطالب (مطلوب للـ Validator الجديد)
        StudentEnrollment::factory()->create([
            'student_id' => $activeStudent->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'class_section_id' => $section->id,
            'status' => EnrollmentStatus::Active,
        ]);

        AnnualResult::create([
            'student_id' => $activeStudent->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'decision' => 'pass',
        ]);

        // ❌ لا يوجد Promotion - يجب أن يمنع الإغلاق

        $result = app(AcademicYearClosureValidator::class)->validate($year);

        $this->assertFalse($result['can']);
        $this->assertNotEmpty($result['issues']);
    }
}

