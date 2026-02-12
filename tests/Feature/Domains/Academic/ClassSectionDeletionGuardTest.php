<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\ClassSection\Actions\DeleteClassSectionAction;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Infrastructure\Exceptions\CannotDeleteException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassSectionDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_deleting_section_with_related_records(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);

        $student = Student::factory()->create([
            'current_class_section_id' => $section->id,
            'current_grade_id' => $grade->id,
        ]);

        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'class_section_id' => $section->id,
        ]);

        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
        ]);

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'class_section_id' => $section->id,
            'term_id' => $term->id,
        ]);

        Timetable::factory()
            ->forCourseOffering($courseOffering)
            ->forClassSection($section)
            ->create();

        Attendance::factory()
            ->forClassSection($section)
            ->create([
                'student_id' => $student->id,
                'academic_year_id' => $year->id,
                'term_id' => $term->id,
            ]);

        $processedBy = User::factory()->create();
        Promotion::create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'annual_result_id' => null,
            'from_grade_id' => $grade->id,
            'to_grade_id' => null,
            'to_class_section_id' => $section->id,
            'type' => 'promoted',
            'has_financial_clearance' => false,
            'certificate_blocked' => false,
            'is_reverted' => false,
            'reverted_by' => null,
            'reverted_at' => null,
            'revert_reason' => null,
            'notes' => null,
            'processed_by' => $processedBy->id,
            'processed_at' => now(),
        ]);

        $action = app(DeleteClassSectionAction::class);

        try {
            $action->execute($section);
            $this->fail('Expected CannotDeleteException was not thrown.');
        } catch (CannotDeleteException $exception) {
            $blockers = $exception->getBlockers();

            $this->assertContains('طلاب (1)', $blockers);
            $this->assertContains('تسجيلات طلاب (1)', $blockers);
            $this->assertContains('عروض مواد (1)', $blockers);
            $this->assertContains('جداول الحصص (1)', $blockers);
            $this->assertContains('سجلات حضور (1)', $blockers);
            $this->assertContains('قرارات ترحيل (1)', $blockers);
        }
    }
}
