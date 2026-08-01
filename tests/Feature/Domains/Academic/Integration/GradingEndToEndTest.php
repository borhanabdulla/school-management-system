<?php

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Control\Models\ControlMark;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Grading\Actions\CalculateTermGradesAction;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Services\GradingConfigHealthChecker;
use App\Domains\Academic\Grading\Services\GradeSyncService;
use App\Domains\Academic\Grading\Services\GradeScaleValidator;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Attendance\Events\AttendanceBatchSaved;
use App\Domains\Academic\Grading\Listeners\SyncAttendanceToMonthlyGrade;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Promotion\Services\AnnualResultService;
use App\Domains\Academic\Results\Models\AnnualResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    SystemSetting::set('grading.scale', [
        ['min' => 0, 'max' => 59, 'grade' => 'F'],
        ['min' => 59, 'max' => 69, 'grade' => 'D'],
        ['min' => 69, 'max' => 79, 'grade' => 'C'],
        ['min' => 79, 'max' => 89, 'grade' => 'B'],
        ['min' => 89, 'max' => 100, 'grade' => 'A'],
    ], 'grading', 'json');
});

test('pr10 scenario A: term1 monthly grades + final exam produce correct term result', function () {
    $academicYear = AcademicYear::factory()->create();
    $term1 = Term::factory()->firstTerm()->create(['academic_year_id' => $academicYear->id]);
    Term::factory()->secondTerm()->create(['academic_year_id' => $academicYear->id]);

    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
    ]);

    $categoryKey = 'homework';
    $categoryLabel = 'واجبات';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $categoryKey, 'label' => $categoryLabel, 'max_score' => 10],
        ])]
    );

    $config = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $grade->id)
        ->where('term_id', $term1->id)
        ->firstOrFail();

    $template = $config->template;
    $monthlyCategory = $template->categories()->where('is_final_exam', false)->firstOrFail();
    $finalExamCategory = $template->categories()->where('is_final_exam', true)->firstOrFail();

    $monthlyCategory->update([
        'mapping_type' => 'manual',
        'weight' => 10,
        'max_raw_score' => 10,
        'pass_required' => false,
        'pass_threshold' => null,
    ]);

    $finalExamCategory->update([
        'weight' => 90,
        'pass_required' => false,
        'pass_threshold' => null,
    ]);

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $monthlyCategory->id,
        'aggregation_rule' => 'avg',
        'missing_months_policy' => 'ignore',
    ]);

    $student = Student::factory()->create([
        'current_grade_id' => $grade->id,
        'current_class_section_id' => $classSection->id,
        'status' => 'active',
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'class_section_id' => $classSection->id,
        'status' => 'active',
    ]);

    $month1 = GradebookMonth::factory()->forTerm($term1)->withOrder(1)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'سبتمبر',
        'start_date' => '2025-09-01',
        'end_date' => '2025-09-30',
    ]);
    $month2 = GradebookMonth::factory()->forTerm($term1)->withOrder(2)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'أكتوبر',
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
    ]);

    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month1->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 8,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month2->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 6,
        'max_score' => 10,
    ]);

    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $monthlyCategory->id)
        ->where('term_id', $term1->id)
        ->first();

    expect($mark)->not->toBeNull();
    expect((float) $mark->raw_score)->toBe(7.0);
    expect((float) $mark->scaled_score)->toBe(7.0);

    $session = ExamSession::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
    ]);

    $seating = ExamSeating::factory()->create([
        'exam_session_id' => $session->id,
        'student_id' => $student->id,
    ]);

    ControlMark::create([
        'exam_seating_id' => $seating->id,
        'course_offering_id' => $courseOffering->id,
        'score' => 40,
        'is_absent' => false,
    ]);

    $report = app(GradingConfigHealthChecker::class)->checkTerm($term1)->toArray();
    expect($report['invalid'])->toBe([]);

    $action = app(CalculateTermGradesAction::class);
    $action->execute($classSection, $term1);

    $termResult = TermResult::where('student_id', $student->id)
        ->where('term_id', $term1->id)
        ->where('course_offering_id', $courseOffering->id)
        ->first();

    expect($termResult)->not->toBeNull();
    expect((float) $termResult->coursework_score)->toBe(7.0);
    expect((float) $termResult->exam_score)->toBe(40.0);
    expect((float) $termResult->total_score)->toBe(47.0);
});

test('pr10 scenario B: term2 data does not mutate term1 results', function () {
    $academicYear = AcademicYear::factory()->create();
    $term1 = Term::factory()->firstTerm()->create(['academic_year_id' => $academicYear->id]);
    $term2 = Term::factory()->secondTerm()->create(['academic_year_id' => $academicYear->id]);

    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
    ]);

    $categoryKey = 'homework';
    $categoryLabel = 'واجبات';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $categoryKey, 'label' => $categoryLabel, 'max_score' => 10],
        ])]
    );

    $configTerm1 = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $grade->id)
        ->where('term_id', $term1->id)
        ->firstOrFail();

    $template = $configTerm1->template;
    $monthlyCategory = $template->categories()->where('is_final_exam', false)->firstOrFail();
    $finalExamCategory = $template->categories()->where('is_final_exam', true)->firstOrFail();

    $monthlyCategory->update([
        'mapping_type' => 'manual',
        'weight' => 10,
        'max_raw_score' => 10,
        'pass_required' => false,
        'pass_threshold' => null,
    ]);

    $finalExamCategory->update([
        'weight' => 90,
        'pass_required' => false,
        'pass_threshold' => null,
    ]);

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $monthlyCategory->id,
        'aggregation_rule' => 'avg',
        'missing_months_policy' => 'ignore',
    ]);

    $student = Student::factory()->create([
        'current_grade_id' => $grade->id,
        'current_class_section_id' => $classSection->id,
        'status' => 'active',
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'class_section_id' => $classSection->id,
        'status' => 'active',
    ]);

    $term1Month1 = GradebookMonth::factory()->forTerm($term1)->withOrder(1)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'سبتمبر',
        'start_date' => '2025-09-01',
        'end_date' => '2025-09-30',
    ]);
    $term1Month2 = GradebookMonth::factory()->forTerm($term1)->withOrder(2)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'أكتوبر',
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
    ]);

    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term1Month1->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 8,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term1Month2->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 6,
        'max_score' => 10,
    ]);

    $term1Session = ExamSession::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
    ]);

    $term1Seating = ExamSeating::factory()->create([
        'exam_session_id' => $term1Session->id,
        'student_id' => $student->id,
    ]);

    ControlMark::create([
        'exam_seating_id' => $term1Seating->id,
        'course_offering_id' => $courseOffering->id,
        'score' => 40,
        'is_absent' => false,
    ]);

    $action = app(CalculateTermGradesAction::class);
    $action->execute($classSection, $term1);

    $term1Result = TermResult::where('student_id', $student->id)
        ->where('term_id', $term1->id)
        ->where('course_offering_id', $courseOffering->id)
        ->firstOrFail();

    // Switch offering to term 2 (same offering id).
    $courseOffering->update(['term_id' => $term2->id]);

    $configTerm2 = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $grade->id)
        ->where('term_id', $term2->id)
        ->first();

    if (! $configTerm2) {
        SubjectGradingConfig::create([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term2->id,
            'grading_template_id' => $template->id,
            'max_score' => 100,
            'pass_score' => 50,
            'is_continuous' => true,
            'counts_in_gpa' => true,
        ]);
    }

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term2->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $monthlyCategory->id,
        'aggregation_rule' => 'avg',
        'missing_months_policy' => 'ignore',
    ]);

    $term2Month1 = GradebookMonth::factory()->forTerm($term2)->withOrder(1)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'نوفمبر',
        'start_date' => '2025-11-01',
        'end_date' => '2025-11-30',
    ]);
    $term2Month2 = GradebookMonth::factory()->forTerm($term2)->withOrder(2)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'ديسمبر',
        'start_date' => '2025-12-01',
        'end_date' => '2025-12-31',
    ]);

    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term2Month1->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 4,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term2Month2->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 3,
        'max_score' => 10,
    ]);

    $term2Session = ExamSession::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term2->id,
    ]);

    $term2Seating = ExamSeating::factory()->create([
        'exam_session_id' => $term2Session->id,
        'student_id' => $student->id,
    ]);

    ControlMark::create([
        'exam_seating_id' => $term2Seating->id,
        'course_offering_id' => $courseOffering->id,
        'score' => 20,
        'is_absent' => false,
    ]);

    $action->execute($classSection, $term2);

    $term1ResultFresh = TermResult::where('student_id', $student->id)
        ->where('term_id', $term1->id)
        ->where('course_offering_id', $courseOffering->id)
        ->firstOrFail();

    expect((float) $term1ResultFresh->coursework_score)->toBe((float) $term1Result->coursework_score);
    expect((float) $term1ResultFresh->exam_score)->toBe((float) $term1Result->exam_score);
    expect((float) $term1ResultFresh->total_score)->toBe((float) $term1Result->total_score);

    $term2Result = TermResult::where('student_id', $student->id)
        ->where('term_id', $term2->id)
        ->where('course_offering_id', $courseOffering->id)
        ->firstOrFail();

    expect((float) $term2Result->coursework_score)->toBe(3.5);
    expect((float) $term2Result->exam_score)->toBe(20.0);
    expect((float) $term2Result->total_score)->toBe(23.5);
});

test('pr10 scenario C: annual aggregation sums term results correctly', function () {
    $academicYear = AcademicYear::factory()->create();
    $term1 = Term::factory()->firstTerm()->create(['academic_year_id' => $academicYear->id]);
    $term2 = Term::factory()->secondTerm()->create(['academic_year_id' => $academicYear->id]);

    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
    ]);

    $categoryKey = 'homework';
    $categoryLabel = 'واجبات';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $categoryKey, 'label' => $categoryLabel, 'max_score' => 10],
        ])]
    );

    $configTerm1 = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $grade->id)
        ->where('term_id', $term1->id)
        ->firstOrFail();

    $template = $configTerm1->template;
    $monthlyCategory = $template->categories()->where('is_final_exam', false)->firstOrFail();
    $finalExamCategory = $template->categories()->where('is_final_exam', true)->firstOrFail();

    $monthlyCategory->update([
        'mapping_type' => 'manual',
        'weight' => 10,
        'max_raw_score' => 10,
        'pass_required' => false,
        'pass_threshold' => null,
    ]);

    $finalExamCategory->update([
        'weight' => 90,
        'pass_required' => false,
        'pass_threshold' => null,
    ]);

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $monthlyCategory->id,
        'aggregation_rule' => 'avg',
        'missing_months_policy' => 'ignore',
    ]);

    $student = Student::factory()->create([
        'current_grade_id' => $grade->id,
        'current_class_section_id' => $classSection->id,
        'status' => 'active',
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'class_section_id' => $classSection->id,
        'status' => 'active',
    ]);

    $term1Month1 = GradebookMonth::factory()->forTerm($term1)->withOrder(1)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'سبتمبر',
        'start_date' => '2025-09-01',
        'end_date' => '2025-09-30',
    ]);
    $term1Month2 = GradebookMonth::factory()->forTerm($term1)->withOrder(2)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'أكتوبر',
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
    ]);

    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term1Month1->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 8,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term1Month2->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 6,
        'max_score' => 10,
    ]);

    $term1Session = ExamSession::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
    ]);

    $term1Seating = ExamSeating::factory()->create([
        'exam_session_id' => $term1Session->id,
        'student_id' => $student->id,
    ]);

    ControlMark::create([
        'exam_seating_id' => $term1Seating->id,
        'course_offering_id' => $courseOffering->id,
        'score' => 40,
        'is_absent' => false,
    ]);

    $action = app(CalculateTermGradesAction::class);
    $action->execute($classSection, $term1);

    $courseOffering->update(['term_id' => $term2->id]);

    $configTerm2 = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $grade->id)
        ->where('term_id', $term2->id)
        ->first();

    if (! $configTerm2) {
        SubjectGradingConfig::create([
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'term_id' => $term2->id,
            'grading_template_id' => $template->id,
            'max_score' => 100,
            'pass_score' => 50,
            'is_continuous' => true,
            'counts_in_gpa' => true,
        ]);
    }

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term2->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $monthlyCategory->id,
        'aggregation_rule' => 'avg',
        'missing_months_policy' => 'ignore',
    ]);

    $term2Month1 = GradebookMonth::factory()->forTerm($term2)->withOrder(1)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'نوفمبر',
        'start_date' => '2025-11-01',
        'end_date' => '2025-11-30',
    ]);
    $term2Month2 = GradebookMonth::factory()->forTerm($term2)->withOrder(2)->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'ديسمبر',
        'start_date' => '2025-12-01',
        'end_date' => '2025-12-31',
    ]);

    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term2Month1->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 4,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $term2Month2->id,
        'template_category_id' => $monthlyCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 3,
        'max_score' => 10,
    ]);

    $term2Session = ExamSession::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term2->id,
    ]);

    $term2Seating = ExamSeating::factory()->create([
        'exam_session_id' => $term2Session->id,
        'student_id' => $student->id,
    ]);

    ControlMark::create([
        'exam_seating_id' => $term2Seating->id,
        'course_offering_id' => $courseOffering->id,
        'score' => 20,
        'is_absent' => false,
    ]);

    $action->execute($classSection, $term2);

    $term1Results = TermResult::where('student_id', $student->id)
        ->where('term_id', $term1->id)
        ->get();

    $term2Results = TermResult::where('student_id', $student->id)
        ->where('term_id', $term2->id)
        ->get();

    $term1Total = (float) $term1Results->sum('total_score');
    $term1Max = (float) $term1Results->sum('max_score');
    $term2Total = (float) $term2Results->sum('total_score');
    $term2Max = (float) $term2Results->sum('max_score');
    $annualTotal = ($term1Total * 0.5) + ($term2Total * 0.5);
    $annualMax = ($term1Max * 0.5) + ($term2Max * 0.5);
    $expectedPercentage = $annualMax > 0 ? round(($annualTotal / $annualMax) * 100, 2) : 0;

    $service = app(AnnualResultService::class);
    $count = $service->aggregateTermResults($academicYear);
    expect($count)->toBe(1);

    $annual = AnnualResult::where('student_id', $student->id)
        ->where('academic_year_id', $academicYear->id)
        ->first();

    expect($annual)->not->toBeNull();
    expect((float) $annual->term1_total)->toBe($term1Total);
    expect((float) $annual->term1_max)->toBe($term1Max);
    expect((float) $annual->term2_total)->toBe($term2Total);
    expect((float) $annual->term2_max)->toBe($term2Max);
    expect((float) $annual->annual_total)->toBe($annualTotal);
    expect((float) $annual->annual_max)->toBe($annualMax);
    expect((float) $annual->percentage)->toBe($expectedPercentage);
});

test('full grading pipeline: settings -> monthly + attendance + homework -> term results -> annual decision', function () {
    $academicYear = AcademicYear::factory()->active()->create();
    $term1 = Term::factory()->firstTerm()->create([
        'academic_year_id' => $academicYear->id,
        'status' => TermStatus::Active,
    ]);
    $term2 = Term::factory()->secondTerm()->create([
        'academic_year_id' => $academicYear->id,
        'status' => TermStatus::Pending,
    ]);

    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $offering1 = CourseOffering::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
    ]);
    $offering2 = CourseOffering::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term2->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
    ]);

    $studentPass = Student::factory()->create(['current_class_section_id' => $classSection->id]);
    $studentFail = Student::factory()->create(['current_class_section_id' => $classSection->id]);

    StudentEnrollment::create([
        'student_id' => $studentPass->id,
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'class_section_id' => $classSection->id,
        'enrollment_date' => now(),
        'enrollment_type' => 'new',
        'status' => \App\Domains\Academic\Student\Enums\EnrollmentStatus::Active,
    ]);
    StudentEnrollment::create([
        'student_id' => $studentFail->id,
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'class_section_id' => $classSection->id,
        'enrollment_date' => now(),
        'enrollment_type' => 'new',
        'status' => \App\Domains\Academic\Student\Enums\EnrollmentStatus::Active,
    ]);

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        [
            'monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
                ['key' => 'homework', 'label' => 'واجبات', 'max_score' => 10, 'is_default' => true],
                ['key' => 'attendance', 'label' => 'مواظبة', 'max_score' => 5, 'is_default' => true, 'is_attendance' => true],
            ]),
            'attendance_deduct_after' => 0,
            'attendance_deduct_per_absence' => 1,
            'attendance_max_score' => 5,
        ]
    );

    $config1 = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $grade->id)
        ->where('term_id', $term1->id)
        ->with('template.categories')
        ->firstOrFail();
    $template1 = $config1->template;

    $coursework1 = $template1->categories()->where('is_final_exam', false)->firstOrFail();
    $final1 = $template1->categories()->where('is_final_exam', true)->firstOrFail();
    $attendance1 = TemplateCategory::create([
        'grading_template_id' => $template1->id,
        'name' => 'مواظبة',
        'weight' => 10,
        'max_raw_score' => 5,
        'calculation_type' => 'sum',
        'is_dynamic_weight' => false,
        'is_locked' => false,
        'pass_required' => false,
        'pass_threshold' => null,
        'order' => 3,
        'mapping_type' => 'attendance',
        'is_readonly' => false,
        'is_final_exam' => false,
    ]);

    $coursework1->update([
        'weight' => 50,
        'mapping_type' => 'manual',
        'max_raw_score' => 10,
    ]);
    $final1->update([
        'weight' => 40,
        'max_raw_score' => 40,
    ]);

    foreach (['homework' => $coursework1->id, 'attendance' => $attendance1->id] as $key => $categoryId) {
        MonthlyCategoryMapping::updateOrCreate([
            'academic_year_id' => $academicYear->id,
            'term_id' => $term1->id,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'category_key' => $key,
        ], [
            'template_category_id' => $categoryId,
            'aggregation_rule' => 'sum',
            'missing_months_policy' => 'ignore',
        ]);
    }

    $month1 = GradebookMonth::factory()->create([
        'term_id' => $term1->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'سبتمبر',
        'start_date' => '2025-09-01',
        'end_date' => '2025-09-30',
        'order' => 1,
    ]);

    MonthlyGrade::create([
        'student_id' => $studentPass->id,
        'course_offering_id' => $offering1->id,
        'gradebook_month_id' => $month1->id,
        'template_category_id' => $coursework1->id,
        'category_key' => 'homework',
        'category' => 'واجبات',
        'score' => 9,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $studentFail->id,
        'course_offering_id' => $offering1->id,
        'gradebook_month_id' => $month1->id,
        'template_category_id' => $coursework1->id,
        'category_key' => 'homework',
        'category' => 'واجبات',
        'score' => 3,
        'max_score' => 10,
    ]);

    Attendance::create([
        'student_id' => $studentPass->id,
        'class_section_id' => $classSection->id,
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'date' => '2025-09-10',
        'status' => AttendanceStatus::ABSENT->value,
    ]);
    Attendance::create([
        'student_id' => $studentFail->id,
        'class_section_id' => $classSection->id,
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
        'date' => '2025-09-10',
        'status' => AttendanceStatus::ABSENT->value,
    ]);

    $timetable1 = Timetable::factory()->create([
        'class_section_id' => $classSection->id,
        'course_offering_id' => $offering1->id,
        'term_id' => $term1->id,
    ]);

    app(SyncAttendanceToMonthlyGrade::class)->handle(
        new AttendanceBatchSaved($timetable1, '2025-09-10', [$studentPass->id, $studentFail->id])
    );

    $assessment = Assessment::create([
        'course_offering_id' => $offering1->id,
        'template_category_id' => $coursework1->id,
        'title' => 'واجب 1',
        'max_score' => 20,
        'weight' => 20,
    ]);
    $homework = Homework::create([
        'course_offering_id' => $offering1->id,
        'assessment_id' => $assessment->id,
        'title' => 'واجب 1',
        'max_score' => 20,
        'submission_type' => SubmissionType::ONLINE->value,
    ]);

    $service = app(GradeSyncService::class);
    $service->syncHomeworkGrade(HomeworkSubmission::create([
        'homework_id' => $homework->id,
        'student_id' => $studentPass->id,
        'score' => 18,
    ]));
    $service->syncHomeworkGrade(HomeworkSubmission::create([
        'homework_id' => $homework->id,
        'student_id' => $studentFail->id,
        'score' => 8,
    ]));

    $session1 = ExamSession::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term1->id,
    ]);
    $seating1Pass = ExamSeating::factory()->create([
        'exam_session_id' => $session1->id,
        'student_id' => $studentPass->id,
    ]);
    $seating1Fail = ExamSeating::factory()->create([
        'exam_session_id' => $session1->id,
        'student_id' => $studentFail->id,
    ]);

    ControlMark::create([
        'exam_seating_id' => $seating1Pass->id,
        'course_offering_id' => $offering1->id,
        'score' => 35,
    ]);
    ControlMark::create([
        'exam_seating_id' => $seating1Fail->id,
        'course_offering_id' => $offering1->id,
        'score' => 15,
    ]);

    app(CalculateTermGradesAction::class)->execute($classSection, $term1);

    $term1Pass = TermResult::where('student_id', $studentPass->id)
        ->where('term_id', $term1->id)
        ->firstOrFail();
    $term1Fail = TermResult::where('student_id', $studentFail->id)
        ->where('term_id', $term1->id)
        ->firstOrFail();

    expect($term1Pass->is_passed)->toBeTrue();
    expect($term1Fail->is_passed)->toBeFalse();

    $config2 = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $grade->id)
        ->where('term_id', $term2->id)
        ->with('template.categories')
        ->firstOrFail();
    $template2 = $config2->template;

    $coursework2 = $template2->categories()->where('is_final_exam', false)->firstOrFail();
    $final2 = $template2->categories()->where('is_final_exam', true)->firstOrFail();
    $attendance2 = TemplateCategory::create([
        'grading_template_id' => $template2->id,
        'name' => 'مواظبة',
        'weight' => 10,
        'max_raw_score' => 5,
        'calculation_type' => 'sum',
        'is_dynamic_weight' => false,
        'is_locked' => false,
        'pass_required' => false,
        'pass_threshold' => null,
        'order' => 3,
        'mapping_type' => 'attendance',
        'is_readonly' => false,
        'is_final_exam' => false,
    ]);

    $coursework2->update([
        'weight' => 50,
        'mapping_type' => 'manual',
        'max_raw_score' => 10,
    ]);
    $final2->update([
        'weight' => 40,
        'max_raw_score' => 40,
    ]);

    foreach (['homework' => $coursework2->id, 'attendance' => $attendance2->id] as $key => $categoryId) {
        MonthlyCategoryMapping::updateOrCreate([
            'academic_year_id' => $academicYear->id,
            'term_id' => $term2->id,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'category_key' => $key,
        ], [
            'template_category_id' => $categoryId,
            'aggregation_rule' => 'sum',
            'missing_months_policy' => 'ignore',
        ]);
    }

    $month2 = GradebookMonth::factory()->create([
        'term_id' => $term2->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'أكتوبر',
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
        'order' => 1,
    ]);

    MonthlyGrade::create([
        'student_id' => $studentPass->id,
        'course_offering_id' => $offering2->id,
        'gradebook_month_id' => $month2->id,
        'template_category_id' => $coursework2->id,
        'category_key' => 'homework',
        'category' => 'واجبات',
        'score' => 8,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $studentFail->id,
        'course_offering_id' => $offering2->id,
        'gradebook_month_id' => $month2->id,
        'template_category_id' => $coursework2->id,
        'category_key' => 'homework',
        'category' => 'واجبات',
        'score' => 2,
        'max_score' => 10,
    ]);

    $timetable2 = Timetable::factory()->create([
        'class_section_id' => $classSection->id,
        'course_offering_id' => $offering2->id,
        'term_id' => $term2->id,
    ]);

    app(SyncAttendanceToMonthlyGrade::class)->handle(
        new AttendanceBatchSaved($timetable2, '2025-10-10', [$studentPass->id, $studentFail->id])
    );

    $assessment2 = Assessment::create([
        'course_offering_id' => $offering2->id,
        'template_category_id' => $coursework2->id,
        'title' => 'واجب 2',
        'max_score' => 20,
        'weight' => 20,
    ]);
    $homework2 = Homework::create([
        'course_offering_id' => $offering2->id,
        'assessment_id' => $assessment2->id,
        'title' => 'واجب 2',
        'max_score' => 20,
        'submission_type' => SubmissionType::ONLINE->value,
    ]);

    $service->syncHomeworkGrade(HomeworkSubmission::create([
        'homework_id' => $homework2->id,
        'student_id' => $studentPass->id,
        'score' => 16,
    ]));
    $service->syncHomeworkGrade(HomeworkSubmission::create([
        'homework_id' => $homework2->id,
        'student_id' => $studentFail->id,
        'score' => 6,
    ]));

    $session2 = ExamSession::factory()->create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term2->id,
    ]);
    $seating2Pass = ExamSeating::factory()->create([
        'exam_session_id' => $session2->id,
        'student_id' => $studentPass->id,
    ]);
    $seating2Fail = ExamSeating::factory()->create([
        'exam_session_id' => $session2->id,
        'student_id' => $studentFail->id,
    ]);

    ControlMark::create([
        'exam_seating_id' => $seating2Pass->id,
        'course_offering_id' => $offering2->id,
        'score' => 30,
    ]);
    ControlMark::create([
        'exam_seating_id' => $seating2Fail->id,
        'course_offering_id' => $offering2->id,
        'score' => 10,
    ]);

    app(CalculateTermGradesAction::class)->execute($classSection, $term2);

    $annualService = app(AnnualResultService::class);
    $annualService->aggregateTermResults($academicYear);
    $annualService->calculateDecisions($academicYear);

    $annualPass = AnnualResult::where('student_id', $studentPass->id)
        ->where('academic_year_id', $academicYear->id)
        ->firstOrFail();
    $annualFail = AnnualResult::where('student_id', $studentFail->id)
        ->where('academic_year_id', $academicYear->id)
        ->firstOrFail();

    expect($annualPass->decision->value)->toBe('pass');
    expect($annualFail->decision->value)->toBe('conditional');
});
