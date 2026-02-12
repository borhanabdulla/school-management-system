<?php

namespace Tests\Feature\Domains\Academic\Control;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Control\Models\ControlMark;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Control\Services\ResultProcessingService;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Exceptions\InvalidGradingConfigException;
use App\Domains\Academic\Grading\Exceptions\MissingSubjectConfigException;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Results\Models\FinalResult;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResultProcessingServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_coursework_using_flags_only(): void
    {
        $context = $this->buildContext();
        $student = $context['student'];
        $courseOffering = $context['courseOffering'];
        $session = $context['session'];

        $finalCategory = TemplateCategory::factory()->create([
            'is_final_exam' => true,
        ]);

        $namedFinalButNotFlagged = TemplateCategory::factory()->create([
            'name' => 'Final Exam',
            'is_final_exam' => false,
        ]);

        $courseworkCategory = TemplateCategory::factory()->create([
            'is_final_exam' => false,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $finalCategory->id,
            'term_id' => $session->term_id,
            'scaled_score' => 50,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $namedFinalButNotFlagged->id,
            'term_id' => $session->term_id,
            'scaled_score' => 10,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $courseworkCategory->id,
            'term_id' => $session->term_id,
            'scaled_score' => 20,
        ]);

        $service = app(ResultProcessingService::class);
        $result = $service->calculateResult(
            $session,
            $student->id,
            $courseOffering->id
        );

        $this->assertEquals(30.0, $result->coursework_score);
    }

    #[Test]
    public function it_scopes_coursework_by_term_id(): void
    {
        $context = $this->buildContext();
        $student = $context['student'];
        $courseOffering = $context['courseOffering'];
        $session = $context['session'];

        $otherTerm = Term::factory()->create([
            'academic_year_id' => $context['academicYear']->id,
        ]);

        $termCategory = TemplateCategory::factory()->create([
            'is_final_exam' => false,
        ]);

        $otherTermCategory = TemplateCategory::factory()->create([
            'is_final_exam' => false,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $termCategory->id,
            'term_id' => $session->term_id,
            'scaled_score' => 15,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $otherTermCategory->id,
            'term_id' => $otherTerm->id,
            'scaled_score' => 90,
        ]);

        $service = app(ResultProcessingService::class);
        $result = $service->calculateResult(
            $session,
            $student->id,
            $courseOffering->id
        );

        $this->assertEquals(15.0, $result->coursework_score);
    }

    #[Test]
    public function it_records_threshold_failures_on_term_result(): void
    {
        $context = $this->buildContext();
        $student = $context['student'];
        $courseOffering = $context['courseOffering'];
        $session = $context['session'];

        $template = GradingTemplate::factory()->create([
            'total_max_score' => 100,
            'pass_score' => 50,
        ]);

        $finalCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Final Exam',
            'weight' => 60,
            'pass_required' => true,
            'pass_threshold' => 50,
            'is_final_exam' => true,
            'parent_id' => null,
        ]);

        TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Coursework',
            'weight' => 40,
            'pass_required' => false,
            'pass_threshold' => null,
            'is_final_exam' => false,
            'parent_id' => null,
        ]);

        SubjectGradingConfig::updateOrCreate([
            'subject_id' => $courseOffering->subject_id,
            'grade_id' => $courseOffering->classSection->grade_id,
            'term_id' => $session->term_id,
        ], [
            'grading_template_id' => $template->id,
            'max_score' => 100,
            'pass_score' => 50,
            'counts_in_gpa' => true,
        ]);

        $seating = ExamSeating::where('exam_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        ControlMark::where('exam_seating_id', $seating->id)
            ->where('course_offering_id', $courseOffering->id)
            ->update(['score' => 20]);

        $service = app(ResultProcessingService::class);
        $service->calculateResult($session, $student->id, $courseOffering->id);

        $termResult = TermResult::where('student_id', $student->id)
            ->where('course_offering_id', $courseOffering->id)
            ->where('term_id', $session->term_id)
            ->with('failures')
            ->first();

        $this->assertNotNull($termResult);
        $this->assertCount(1, $termResult->failures);
        $this->assertEquals($finalCategory->id, $termResult->failures->first()->template_category_id);
    }

    #[Test]
    public function it_prevents_processing_when_subject_grading_config_missing_and_persists_nothing(): void
    {
        $context = $this->buildContext();
        $student = $context['student'];
        $courseOffering = $context['courseOffering'];
        $session = $context['session'];

        $finalBefore = FinalResult::count();
        $termBefore = TermResult::count();

        SubjectGradingConfig::where([
            'subject_id' => $courseOffering->subject_id,
            'grade_id' => $courseOffering->classSection->grade_id,
            'term_id' => $session->term_id,
        ])->delete();

        $service = app(ResultProcessingService::class);

        $this->expectException(\App\Infrastructure\Exceptions\InvalidOperationException::class);
        $this->expectExceptionMessage('إعدادات الدرجات غير مكتملة');

        $service->calculateResult($session, $student->id, $courseOffering->id);

        $this->assertSame($finalBefore, FinalResult::count());
        $this->assertSame($termBefore, TermResult::count());
    }

    #[Test]
    public function it_rejects_processing_when_template_is_invalid_with_weights(): void
    {
        $context = $this->buildContext();
        $student = $context['student'];
        $courseOffering = $context['courseOffering'];
        $session = $context['session'];

        $template = GradingTemplate::factory()->create([
            'grade_id' => $courseOffering->classSection->grade_id,
            'academic_year_id' => $context['academicYear']->id,
            'total_max_score' => 100,
            'pass_score' => 60,
        ]);

        $coursework = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Coursework',
            'weight' => 40,
            'pass_required' => false,
            'is_final_exam' => false,
            'parent_id' => null,
        ]);

        $finalExam = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Final',
            'weight' => 30, // sums to 70 < 100
            'pass_required' => true,
            'pass_threshold' => 50,
            'is_final_exam' => true,
            'parent_id' => null,
        ]);

        SubjectGradingConfig::updateOrCreate([
            'subject_id' => $courseOffering->subject_id,
            'grade_id' => $courseOffering->classSection->grade_id,
            'term_id' => $session->term_id,
        ], [
            'grading_template_id' => $template->id,
            'max_score' => 100,
            'pass_score' => 60,
            'counts_in_gpa' => true,
        ]);

        $finalBefore = FinalResult::count();
        $termBefore = TermResult::count();

        $service = app(ResultProcessingService::class);

        $this->expectException(\App\Infrastructure\Exceptions\InvalidOperationException::class);
        $this->expectExceptionMessage('إعدادات الدرجات غير مكتملة');

        $service->calculateResult($session, $student->id, $courseOffering->id);

        $this->assertSame($finalBefore, FinalResult::count());
        $this->assertSame($termBefore, TermResult::count());
    }

    #[Test]
    public function it_prevents_processing_when_subject_grading_config_is_invalid(): void
    {
        $context = $this->buildContext();
        $student = $context['student'];
        $courseOffering = $context['courseOffering'];
        $session = $context['session'];

        $template = GradingTemplate::factory()->create([
            'grade_id' => $courseOffering->classSection->grade_id,
            'academic_year_id' => $context['academicYear']->id,
            'total_max_score' => 100,
            'pass_score' => 60,
        ]);

        $coursework = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Coursework',
            'weight' => 40,
            'pass_required' => false,
            'is_final_exam' => false,
            'parent_id' => null,
        ]);

        $finalExam = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Final',
            'weight' => 30, // sum 70 < 100
            'pass_required' => true,
            'pass_threshold' => 50,
            'is_final_exam' => true,
            'parent_id' => null,
        ]);

        SubjectGradingConfig::updateOrCreate([
            'subject_id' => $courseOffering->subject_id,
            'grade_id' => $courseOffering->classSection->grade_id,
            'term_id' => $session->term_id,
        ], [
            'grading_template_id' => $template->id,
            'max_score' => 100,
            'pass_score' => 60,
            'counts_in_gpa' => true,
        ]);

        $this->assertDatabaseMissing('final_results', [
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
        ]);
        $this->assertDatabaseMissing('term_results', [
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'term_id' => $session->term_id,
        ]);

        $service = app(ResultProcessingService::class);

        $this->expectException(\App\Infrastructure\Exceptions\InvalidOperationException::class);
        $this->expectExceptionMessage('إعدادات الدرجات غير مكتملة');

        $service->calculateResult($session, $student->id, $courseOffering->id);

        $this->assertDatabaseMissing('final_results', [
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
        ]);
        $this->assertDatabaseMissing('term_results', [
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'term_id' => $session->term_id,
        ]);
    }

    #[Test]
    public function it_processes_results_from_marks_to_final_and_term_outcomes(): void
    {
        $context = $this->buildContext();
        $student = $context['student'];
        $courseOffering = $context['courseOffering'];
        $session = $context['session'];

        $template = GradingTemplate::factory()->create([
            'grade_id' => $courseOffering->classSection->grade_id,
            'academic_year_id' => $context['academicYear']->id,
            'total_max_score' => 100,
            'pass_score' => 60,
        ]);

        $coursework = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Coursework',
            'weight' => 40,
            'pass_required' => false,
            'is_final_exam' => false,
            'parent_id' => null,
        ]);

        $finalExam = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Final',
            'weight' => 60,
            'pass_required' => true,
            'pass_threshold' => 50,
            'is_final_exam' => true,
            'parent_id' => null,
        ]);

        SubjectGradingConfig::updateOrCreate([
            'subject_id' => $courseOffering->subject_id,
            'grade_id' => $courseOffering->classSection->grade_id,
            'term_id' => $session->term_id,
        ], [
            'grading_template_id' => $template->id,
            'max_score' => 100,
            'pass_score' => 60,
            'counts_in_gpa' => true,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $coursework->id,
            'term_id' => $session->term_id,
            'scaled_score' => 30,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $finalExam->id,
            'term_id' => $session->term_id,
            'scaled_score' => 40,
        ]);

        ControlMark::where('exam_seating_id', ExamSeating::where('exam_session_id', $session->id)->first()->id)
            ->where('course_offering_id', $courseOffering->id)
            ->update(['score' => 45, 'is_absent' => false]);

        $service = app(ResultProcessingService::class);
        $count = $service->processAll($session);

        $this->assertGreaterThan(0, $count);

        $final = FinalResult::where('exam_session_id', $session->id)
            ->where('student_id', $student->id)
            ->where('course_offering_id', $courseOffering->id)
            ->first();

        $term = TermResult::where('term_id', $session->term_id)
            ->where('student_id', $student->id)
            ->where('course_offering_id', $courseOffering->id)
            ->first();

        $this->assertNotNull($final);
        $this->assertNotNull($term);
        $this->assertEquals(75, $final->total_score);
        $this->assertEquals(75, $term->total_score);
        $this->assertFalse($term->is_passed);
    }

    private function buildContext(): array
    {
        SystemSetting::set('grading.scale', [
            ['min' => 0, 'max' => 59, 'grade' => 'F'],
            ['min' => 59, 'max' => 69, 'grade' => 'D'],
            ['min' => 69, 'max' => 79, 'grade' => 'C'],
            ['min' => 79, 'max' => 89, 'grade' => 'B'],
            ['min' => 89, 'max' => 100, 'grade' => 'A'],
        ], 'grading', 'json');

        $academicYear = AcademicYear::factory()->create(['status' => 'active']);
        $term = Term::factory()->create(['academic_year_id' => $academicYear->id]);

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $academicYear->id,
            'term_id' => $term->id,
        ]);

        $courseOffering->loadMissing(['classSection', 'subject']);
        $config = SubjectGradingConfig::where('subject_id', $courseOffering->subject_id)
            ->where('grade_id', $courseOffering->classSection?->grade_id)
            ->where('term_id', $courseOffering->term_id)
            ->with('template.categories')
            ->firstOrFail();

        $category = $config->template
            ->categories
            ->firstWhere('is_final_exam', false)
            ?? $config->template->categories->first();

        $monthlyCategories = GradebookSettings::normalizeMonthlyCategories([
            ['label' => 'واجبات', 'max_score' => 10, 'is_default' => true],
        ]);

        GradebookSettings::updateOrCreate(
            ['academic_year_id' => $academicYear->id],
            ['monthly_categories' => $monthlyCategories]
        );

        MonthlyCategoryMapping::updateOrCreate([
            'academic_year_id' => $academicYear->id,
            'term_id' => $term->id,
            'grade_id' => $courseOffering->classSection?->grade_id,
            'subject_id' => $courseOffering->subject_id,
            'category_key' => $monthlyCategories[0]['key'],
        ], [
            'template_category_id' => $category?->id,
            'aggregation_rule' => 'sum',
            'missing_months_policy' => 'ignore',
        ]);

        $student = Student::factory()->create();

        $session = ExamSession::factory()->create([
            'academic_year_id' => $academicYear->id,
            'term_id' => $term->id,
        ]);

        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
        ]);

        ControlMark::create([
            'exam_seating_id' => $seating->id,
            'course_offering_id' => $courseOffering->id,
            'score' => 0,
            'is_absent' => false,
        ]);

        return [
            'academicYear' => $academicYear,
            'term' => $term,
            'courseOffering' => $courseOffering,
            'student' => $student,
            'session' => $session,
        ];
    }
}
