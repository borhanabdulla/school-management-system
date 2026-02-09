<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeworkTest extends TestCase
{
    use RefreshDatabase;

    // ============================================
    // اختبارات إنشاء الواجبات
    // ============================================

    /** @test */
    public function can_create_homework(): void
    {
        $courseOffering = CourseOffering::factory()->create();

        $homework = Homework::create([
            'course_offering_id' => $courseOffering->id,
            'title' => 'حل تمارين الفصل الأول',
            'description' => 'حل جميع تمارين الفصل الأول من الكتاب',
            'submission_type' => SubmissionType::ONLINE->value,
            'status' => HomeworkStatus::PUBLISHED->value,
            'due_date' => now()->addDays(7),
            'allow_late' => true,
            'max_score' => 20,
        ]);

        $this->assertDatabaseHas('homeworks', [
            'id' => $homework->id,
            'title' => 'حل تمارين الفصل الأول',
            'status' => 'published',
        ]);
    }

    /** @test */
    public function homework_belongs_to_course_offering(): void
    {
        $courseOffering = CourseOffering::factory()->create();
        $homework = Homework::factory()->forCourseOffering($courseOffering)->create();

        $this->assertEquals($courseOffering->id, $homework->courseOffering->id);
    }

    /** @test */
    public function homework_can_have_submissions(): void
    {
        $homework = Homework::factory()->create();
        $students = Student::factory()->count(3)->create();

        foreach ($students as $student) {
            HomeworkSubmission::factory()
                ->forHomework($homework)
                ->forStudent($student)
                ->submitted()
                ->create();
        }

        $this->assertCount(3, $homework->submissions);
    }

    // ============================================
    // اختبارات التسليم
    // ============================================

    /** @test */
    public function student_can_submit_homework(): void
    {
        $homework = Homework::factory()->online()->create();
        $student = Student::factory()->create();

        $submission = HomeworkSubmission::create([
            'homework_id' => $homework->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::SUBMITTED->value,
            'submitted_at' => now(),
            'file_path' => 'submissions/homework_1_student_1.pdf',
        ]);

        $this->assertDatabaseHas('homework_submissions', [
            'id' => $submission->id,
            'status' => 'submitted',
        ]);
    }

    /** @test */
    public function submission_can_be_graded(): void
    {
        $submission = HomeworkSubmission::factory()
            ->submitted()
            ->create();

        $submission->update([
            'status' => SubmissionStatus::GRADED->value,
            'score' => 18.5,
            'feedback' => 'عمل ممتاز! استمر على هذا المستوى.',
        ]);

        $this->assertEquals(SubmissionStatus::GRADED, $submission->status);
        $this->assertEquals(18.5, $submission->score);
        $this->assertNotNull($submission->feedback);
    }

    /** @test */
    public function late_submission_is_marked_correctly(): void
    {
        $homework = Homework::factory()->create([
            'due_date' => now()->subDays(2),
            'allow_late' => true,
        ]);

        $submission = HomeworkSubmission::factory()
            ->forHomework($homework)
            ->late()
            ->create();

        $this->assertEquals(SubmissionStatus::LATE, $submission->status);
    }

    // ============================================
    // اختبارات الحالة
    // ============================================

    /** @test */
    public function homework_status_enum_has_correct_values(): void
    {
        $this->assertEquals('draft', HomeworkStatus::DRAFT->value);
        $this->assertEquals('published', HomeworkStatus::PUBLISHED->value);
        $this->assertEquals('archived', HomeworkStatus::ARCHIVED->value);
    }

    /** @test */
    public function submission_status_enum_has_correct_values(): void
    {
        $this->assertEquals('pending', SubmissionStatus::PENDING->value);
        $this->assertEquals('submitted', SubmissionStatus::SUBMITTED->value);
        $this->assertEquals('late', SubmissionStatus::LATE->value);
        $this->assertEquals('graded', SubmissionStatus::GRADED->value);
    }

    // ============================================
    // اختبارات الـ Factory
    // ============================================

    /** @test */
    public function homework_factory_creates_valid_record(): void
    {
        $homework = Homework::factory()->create();

        $this->assertDatabaseHas('homeworks', ['id' => $homework->id]);
        $this->assertNotNull($homework->course_offering_id);
        $this->assertNotNull($homework->title);
    }

    /** @test */
    public function homework_factory_draft_state_works(): void
    {
        $homework = Homework::factory()->draft()->create();

        $this->assertEquals(HomeworkStatus::DRAFT, $homework->status);
    }

    /** @test */
    public function submission_factory_creates_valid_record(): void
    {
        $submission = HomeworkSubmission::factory()->create();

        $this->assertDatabaseHas('homework_submissions', ['id' => $submission->id]);
        $this->assertNotNull($submission->homework_id);
        $this->assertNotNull($submission->student_id);
    }

    /** @test */
    public function submission_factory_graded_state_includes_score(): void
    {
        $submission = HomeworkSubmission::factory()->graded(9.5, 'أحسنت!')->create();

        $this->assertEquals(SubmissionStatus::GRADED, $submission->status);
        $this->assertEquals(9.5, $submission->score);
        $this->assertEquals('أحسنت!', $submission->feedback);
    }

    /** @test */
    public function submission_excellent_grade_gives_high_score(): void
    {
        $submission = HomeworkSubmission::factory()->excellentGrade()->create();

        $this->assertGreaterThanOrEqual(9, $submission->score);
    }

    // ============================================
    // اختبارات العلاقات والتكامل
    // ============================================

    /** @test */
    public function unique_submission_per_student_homework(): void
    {
        $homework = Homework::factory()->create();
        $student = Student::factory()->create();

        HomeworkSubmission::factory()
            ->forHomework($homework)
            ->forStudent($student)
            ->create();

        $this->expectException(\Illuminate\Database\QueryException::class);

        HomeworkSubmission::factory()
            ->forHomework($homework)
            ->forStudent($student)
            ->create();
    }
}
