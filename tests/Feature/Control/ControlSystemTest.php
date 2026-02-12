<?php

namespace Tests\Feature\Control;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Control\Models\ControlMark;
use App\Domains\Academic\Results\Models\FinalResult;
use App\Domains\Academic\Results\Enums\FinalResultStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Services\GradingConfigHealthChecker;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\Control\Services\SecrecyService;
use App\Domains\Academic\Control\Services\BlindEntryService;
use App\Domains\Academic\Control\Services\ResultProcessingService;
use App\Domains\Academic\Control\Exceptions\TermMismatchException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected ExamSession $session;
    protected $grade;
    protected $classSection;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::set('grading.scale', [
            ['min' => 0, 'max' => 59, 'grade' => 'F'],
            ['min' => 59, 'max' => 79, 'grade' => 'C'],
            ['min' => 79, 'max' => 100, 'grade' => 'A'],
        ], 'grading', 'json');

        // Create user for authentication
        $this->user = User::factory()->create();

        // Create and assign permission
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'marks.override']);
        $this->user->givePermissionTo('marks.override');

        // Create academic year and term
        $this->academicYear = AcademicYear::factory()->create(['status' => 'active']);
        $this->term = Term::factory()->create(['academic_year_id' => $this->academicYear->id]);

        // Create grade and class section
        $this->grade = Grade::factory()->create();
        $this->classSection = ClassSection::factory()->create([
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Create exam session
        $this->session = ExamSession::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'status' => 'setup',
            'is_active' => false,
        ]);
    }

    private function configureGradingForOffering(CourseOffering $courseOffering): void
    {
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
            ['academic_year_id' => $courseOffering->academic_year_id],
            ['monthly_categories' => $monthlyCategories]
        );

        MonthlyCategoryMapping::updateOrCreate([
            'academic_year_id' => $courseOffering->academic_year_id,
            'term_id' => $courseOffering->term_id,
            'grade_id' => $courseOffering->classSection?->grade_id,
            'subject_id' => $courseOffering->subject_id,
            'category_key' => $monthlyCategories[0]['key'],
        ], [
            'template_category_id' => $category?->id,
            'aggregation_rule' => 'sum',
            'missing_months_policy' => 'ignore',
        ]);
    }

    /** @test */
    public function it_can_access_control_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('control.dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_generate_seat_and_secret_numbers()
    {
        $students = Student::factory()->count(5)->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);

        $secrecyService = app(SecrecyService::class);
        $generated = $secrecyService->generateNumbers(
            $this->session,
            $students->pluck('id'),
            1000
        );

        $this->assertCount(5, $generated);

        // Verify each student has unique seat and secret numbers
        $seatNumbers = $generated->pluck('seat_number')->toArray();
        $secretNumbers = $generated->pluck('secret_number')->toArray();

        $this->assertCount(5, array_unique($seatNumbers));
        $this->assertCount(5, array_unique($secretNumbers));

        // Verify database records
        $this->assertDatabaseCount('exam_seatings', 5);
    }

    /** @test */
    public function it_can_find_seating_by_secret_number()
    {
        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);

        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
            'secret_number' => 'AB1234',
        ]);

        $found = ExamSeating::findBySecretNumber($this->session->id, 'AB1234');

        $this->assertNotNull($found);
        $this->assertEquals($seating->id, $found->id);
    }

    /** @test */
    public function it_rejects_grades_for_inactive_session()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('الدورة الامتحانية غير نشطة');

        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
        ]);

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'class_section_id' => $this->classSection->id,
        ]);
        $this->configureGradingForOffering($courseOffering);

        $report = app(GradingConfigHealthChecker::class)->checkTerm($this->term)->toArray();
        $this->assertEmpty($report['invalid'], json_encode($report['invalid'], JSON_UNESCAPED_UNICODE));

        $blindService = new BlindEntryService();
        $blindService->submitGrade(
            $this->session,
            $seating->secret_number,
            $courseOffering->id,
            85.5
        );
    }

    /** @test */
    public function it_can_submit_grades_when_session_is_active()
    {
        $this->session->update(['status' => 'active', 'is_active' => true]);

        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
        ]);

        $courseOffering = CourseOffering::factory()->create([
            'term_id' => $this->term->id,
        ]);

        $blindService = new BlindEntryService();
        $mark = $blindService->submitGrade(
            $this->session,
            $seating->secret_number,
            $courseOffering->id,
            85.5
        );

        $this->assertInstanceOf(ControlMark::class, $mark);
        $this->assertEquals(85.5, $mark->score);
        $this->assertFalse($mark->is_absent);
    }

    /** @test */
    public function it_rejects_grades_when_course_offering_term_mismatches_session(): void
    {
        $this->expectException(TermMismatchException::class);
        $this->expectExceptionMessage('المادة لا تتبع نفس الترم الخاص بجلسة الكنترول.');

        $this->session->update(['status' => 'active', 'is_active' => true]);

        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
        ]);

        $otherTerm = Term::factory()->create(['academic_year_id' => $this->academicYear->id]);
        $courseOffering = CourseOffering::factory()->create([
            'term_id' => $otherTerm->id,
        ]);

        $blindService = new BlindEntryService();
        $blindService->submitGrade(
            $this->session,
            $seating->secret_number,
            $courseOffering->id,
            85.5
        );
    }

    /** @test */
    public function it_can_mark_student_as_absent()
    {
        $this->session->update(['status' => 'active', 'is_active' => true]);

        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
        ]);

        $courseOffering = CourseOffering::factory()->create([
            'term_id' => $this->term->id,
        ]);

        $blindService = new BlindEntryService();
        $mark = $blindService->submitGrade(
            $this->session,
            $seating->secret_number,
            $courseOffering->id,
            null,
            true
        );

        $this->assertTrue($mark->is_absent);
        $this->assertNull($mark->score);
    }

    /** @test */
    public function it_rejects_invalid_secret_number()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('الرقم السري');

        $this->session->update(['status' => 'active', 'is_active' => true]);

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'class_section_id' => $this->classSection->id,
        ]);
        $this->configureGradingForOffering($courseOffering);

        $report = app(GradingConfigHealthChecker::class)->checkTerm($this->term)->toArray();
        $this->assertEmpty($report['invalid'], json_encode($report['invalid'], JSON_UNESCAPED_UNICODE));

        $blindService = new BlindEntryService();
        $blindService->submitGrade(
            $this->session,
            'INVALID123',
            $courseOffering->id,
            85.5
        );
    }

    /** @test */
    public function it_can_process_final_results()
    {
        $this->session->update(['status' => 'active', 'is_active' => true]);

        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
        ]);

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'class_section_id' => $this->classSection->id,
        ]);
        $this->configureGradingForOffering($courseOffering);

        $report = app(GradingConfigHealthChecker::class)->checkTerm($this->term)->toArray();
        $this->assertEmpty($report['invalid'], json_encode($report['invalid'], JSON_UNESCAPED_UNICODE));

        // Create control mark
        ControlMark::create([
            'exam_seating_id' => $seating->id,
            'course_offering_id' => $courseOffering->id,
            'score' => 85.0,
            'is_absent' => false,
        ]);

        $resultService = app(ResultProcessingService::class);
        $result = $resultService->calculateResult(
            $this->session,
            $student->id,
            $courseOffering->id
        );

        $this->assertInstanceOf(FinalResult::class, $result);
        $this->assertEquals(85.0, $result->final_exam_score);
        $this->assertEquals(FinalResultStatus::Pass, $result->status);
    }

    /** @test */
    public function it_can_publish_results()
    {
        $this->session->update(['status' => 'processing']);

        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'class_section_id' => $this->classSection->id,
        ]);
        $this->configureGradingForOffering($courseOffering);


        FinalResult::create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'coursework_score' => 30,
            'final_exam_score' => 50,
            'total_score' => 80,
            'grade_label' => 'جيد جداً',
            'status' => FinalResultStatus::Pass,
            'is_published' => false,
        ]);

        $resultService = app(ResultProcessingService::class);
        $count = $resultService->publishResults($this->session);

        $this->assertEquals(1, $count);

        $result = FinalResult::where('exam_session_id', $this->session->id)->first();
        $this->assertTrue($result->is_published);
        $this->assertNotNull($result->published_at);
    }

    /** @test */
    public function it_calculates_correct_grade_label()
    {
        $this->assertEquals('A', FinalResult::calculateGradeLabel(95));
        $this->assertEquals('A', FinalResult::calculateGradeLabel(85));
        $this->assertEquals('C', FinalResult::calculateGradeLabel(75));
        $this->assertEquals('C', FinalResult::calculateGradeLabel(65));
        $this->assertEquals('F', FinalResult::calculateGradeLabel(55));
        $this->assertEquals('F', FinalResult::calculateGradeLabel(45));
    }

    /** @test */
    public function it_can_access_seating_print_page()
    {
        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('control.print', [$this->session->id, 'seating']));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_results_viewer()
    {
        $response = $this->actingAs($this->user)
            ->get(route('control.results', $this->session->id));

        $response->assertStatus(200);
    }
    /** @test */
    public function it_can_access_report_card_print_page()
    {
        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('control.print.report-cards', $this->session->id));

        $response->assertStatus(200);
        $response->assertSeeLivewire('admin.control.report-card-print');
    }
    /** @test */
    public function it_can_filter_report_cards_by_class_section()
    {
        $student1 = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student1->id,
        ]);

        // Create another class section and student
        $otherClass = ClassSection::factory()->create(['grade_id' => $this->grade->id]);
        $student2 = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $otherClass->id,
        ]);
        ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student2->id,
        ]);

        // Filter for first class
        $response = $this->actingAs($this->user)
            ->get(route('control.print.report-cards', [
                'sessionId' => $this->session->id,
                'class_section_id' => $this->classSection->id
            ]));

        $response->assertStatus(200);
        $response->assertSee($student1->first_name_ar); // Should see student 1
        $response->assertDontSee($student2->first_name_ar); // Should NOT see student 2
    }
    /** @test */
    public function it_shows_withheld_message_on_report_card()
    {
        $student = Student::factory()->create([
            'current_grade_id' => $this->grade->id,
            'current_class_section_id' => $this->classSection->id,
        ]);
        $seating = ExamSeating::factory()->create([
            'exam_session_id' => $this->session->id,
            'student_id' => $student->id,
            'is_withheld' => true,
            'withhold_reason' => 'Unpaid fees',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('control.print.report-cards', $this->session->id));

        $response->assertStatus(200);
        $response->assertSee('النتيجة محجوبة');
        $response->assertSee('Unpaid fees');
    }
}
