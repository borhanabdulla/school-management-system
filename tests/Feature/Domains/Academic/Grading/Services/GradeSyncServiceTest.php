<?php

use App\Domains\Academic\Grading\Services\GradeSyncService;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Infrastructure\Exceptions\InvalidOperationException;

beforeEach(function () {
    $this->service = app(GradeSyncService::class);

    $this->academicYear = AcademicYear::factory()->create();
    $this->term = Term::factory()->create(['academic_year_id' => $this->academicYear->id]);
    $this->grade = Grade::factory()->create();
    $this->classSection = ClassSection::factory()->create([
        'grade_id' => $this->grade->id,
        'academic_year_id' => $this->academicYear->id,
    ]);
    $this->subject = Subject::factory()->create();

    $this->student = Student::factory()->create();
    $this->courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'term_id' => $this->term->id,
        'class_section_id' => $this->classSection->id,
        'subject_id' => $this->subject->id,
    ]);

    $this->category = TemplateCategory::factory()->create([
        'name' => 'Homework Category',
    ]);

    $this->assessment = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $this->category->id,
        'max_score' => 100,
    ]);

    $this->homework = Homework::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'assessment_id' => $this->assessment->id,
        'max_score' => 50,
    ]);
});

test('it syncs homework score to student mark', function () {
    // Arrange
    $submission = HomeworkSubmission::factory()->create([
        'homework_id' => $this->homework->id,
        'student_id' => $this->student->id,
        'score' => 40, // 40/50 = 80%
    ]);

    // Act
    $this->service->syncHomeworkGrade($submission);

    // Assert
    $mark = StudentMark::where('student_id', $this->student->id)
        ->where('assessment_id', $this->assessment->id)
        ->first();

    expect($mark)->not->toBeNull();
    // Raw score is the obtained total (40), scaled is normalized to 80
    expect((float) $mark->raw_score)->toBe(40.0);
    expect((float) $mark->scaled_score)->toBe(80.0);
});

test('it aggregates multiple homeworks in same assessment', function () {
    // Arrange
    // Homework 1: 40/50 (80%)
    $submission1 = HomeworkSubmission::factory()->create([
        'homework_id' => $this->homework->id,
        'student_id' => $this->student->id,
        'score' => 40,
    ]);

    // Homework 2: 50/50 (100%)
    $hw2 = Homework::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'assessment_id' => $this->assessment->id,
        'max_score' => 50,
    ]);
    $submission2 = HomeworkSubmission::factory()->create([
        'homework_id' => $hw2->id,
        'student_id' => $this->student->id,
        'score' => 50,
    ]);

    // Act
    $this->service->syncHomeworkGrade($submission1);
    $this->service->syncHomeworkGrade($submission2);

    // Assert
    $mark = StudentMark::where('student_id', $this->student->id)
        ->where('assessment_id', $this->assessment->id)
        ->first();

    // Total obtained score: 40 + 50 = 90 (raw), normalized to 90
    expect((float) $mark->raw_score)->toBe(90.0);
    expect((float) $mark->scaled_score)->toBe(90.0);
});

test('it uses assessment weight when provided', function () {
    $this->assessment->update([
        'weight' => 20,
        'max_score' => 100,
    ]);

    $submission = HomeworkSubmission::factory()->create([
        'homework_id' => $this->homework->id,
        'student_id' => $this->student->id,
        'score' => 40, // 40/50 = 80% => 16.0 with weight 20
    ]);

    $this->service->syncHomeworkGrade($submission);

    $mark = StudentMark::where('student_id', $this->student->id)
        ->where('assessment_id', $this->assessment->id)
        ->first();

    expect($mark)->not->toBeNull();
    expect((float) $mark->scaled_score)->toBe(16.0);
});

test('it syncs attendance grade to monthly grade and student mark', function () {
    $academicYear = $this->academicYear;
    $term = $this->term;
    $grade = $this->grade;
    $classSection = $this->classSection;
    $subject = $this->subject;
    $courseOffering = $this->courseOffering;

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    $template = GradingTemplate::factory()->create();
    $attendanceCategory = TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'مواظبة',
        'weight' => 10,
        'mapping_type' => 'attendance',
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 100,
        'pass_score' => 50,
        'counts_in_gpa' => true,
    ]);

    $settings = GradebookSettings::getForYear($academicYear->id);
    $settings->update([
        'attendance_deduct_after' => 0,
        'attendance_deduct_per_absence' => 1,
        'attendance_max_score' => 5,
    ]);

    $normalizedCategories = GradebookSettings::normalizeMonthlyCategories($settings->monthly_categories ?? []);
    $attendanceCategoryConfig = collect($normalizedCategories)->firstWhere('is_attendance', true);
    $attendanceKey = (string) ($attendanceCategoryConfig['key'] ?? 'attendance');
    $attendanceLabel = (string) ($attendanceCategoryConfig['label'] ?? 'مواظبة');

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $attendanceKey,
        'template_category_id' => $attendanceCategory->id,
        'aggregation_rule' => 'sum',
        'missing_months_policy' => 'ignore',
    ]);

    $month = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'سبتمبر',
        'start_date' => '2025-09-01',
        'end_date' => '2025-09-30',
        'order' => 1,
    ]);

    Attendance::factory()->forStudent($student)->forClassSection($classSection)->absent()->forDate('2025-09-05')->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);
    Attendance::factory()->forStudent($student)->forClassSection($classSection)->absent()->forDate('2025-09-10')->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $result = $this->service->syncAttendanceGrade($student, $classSection, $month);

    expect($result->success)->toBeTrue();

    $this->assertDatabaseHas('monthly_grades', [
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month->id,
        'category_key' => $attendanceKey,
        'category' => $attendanceLabel,
        'score' => 3.0,
    ]);

    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $attendanceCategory->id)
        ->first();

    expect($mark)->not->toBeNull();
    expect((float) $mark->raw_score)->toBe(3.0);
    expect((float) $mark->scaled_score)->toBe(6.0);
});

test('it fails attendance sync when multiple attendance categories match', function () {
    $academicYear = $this->academicYear;
    $term = $this->term;
    $grade = $this->grade;
    $classSection = $this->classSection;
    $subject = $this->subject;
    $courseOffering = $this->courseOffering;

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    $template = GradingTemplate::factory()->create();

    TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'مواظبة',
        'weight' => 10,
        'mapping_type' => 'attendance',
    ]);

    TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'مواظبة',
        'weight' => 5,
        'mapping_type' => 'attendance',
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 100,
        'pass_score' => 50,
        'counts_in_gpa' => true,
    ]);

    $month = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'سبتمبر',
        'start_date' => '2025-09-01',
        'end_date' => '2025-09-30',
        'order' => 1,
    ]);

    $result = $this->service->syncAttendanceGrade($student, $classSection, $month);

    expect($result->success)->toBeFalse();

    $this->assertDatabaseMissing('monthly_grades', [
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month->id,
    ]);
});

test('it blocks attendance sync when term is completed', function () {
    $academicYear = AcademicYear::factory()->create();
    $term = Term::factory()->create([
        'academic_year_id' => $academicYear->id,
        'status' => TermStatus::Pending,
    ]);
    $term->update(['status' => TermStatus::Completed]);

    $classSection = ClassSection::factory()->create([
        'academic_year_id' => $academicYear->id,
    ]);
    $student = Student::factory()->create();

    $month = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect(fn () => $this->service->syncAttendanceGrade($student, $classSection, $month))
        ->toThrow(InvalidOperationException::class);
});

test('it aggregates monthly grades using mapping table', function () {
    $academicYear = $this->academicYear;
    $term = Term::factory()->create(['academic_year_id' => $academicYear->id]);
    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $template = GradingTemplate::factory()->create();

    $templateCategory = TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'أعمال السنة',
        'mapping_type' => 'manual',
        'weight' => 10,
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 100,
        'pass_score' => 50,
        'counts_in_gpa' => true,
    ]);

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    $categoryKey = 'homework';
    $categoryLabel = 'واجبات';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $categoryKey, 'label' => $categoryLabel, 'max_score' => 10],
        ])]
    );

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $templateCategory->id,
        'aggregation_rule' => 'sum',
        'missing_months_policy' => 'ignore',
    ]);

    $month = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $monthlyGrade = MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month->id,
        'template_category_id' => $templateCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 8,
        'max_score' => 10,
    ]);

    $result = $this->service->syncFromMonthlyGrade($monthlyGrade);

    expect($result->success)->toBeTrue();

    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $templateCategory->id)
        ->first();

    expect($mark)->not->toBeNull();
});

test('it succeeds when monthly grade label differs from mapping label', function () {
    $academicYear = $this->academicYear;
    $term = Term::factory()->create(['academic_year_id' => $academicYear->id]);
    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $template = GradingTemplate::factory()->create();

    $templateCategory = TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'اسم جديد',
        'mapping_type' => 'manual',
        'weight' => 10,
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 100,
        'pass_score' => 50,
        'counts_in_gpa' => true,
    ]);

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    $categoryKey = 'homework';
    $configuredLabel = 'واجبات';
    $enteredLabel = 'تمارين';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $categoryKey, 'label' => $configuredLabel, 'max_score' => 10],
        ])]
    );

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $templateCategory->id,
        'aggregation_rule' => 'sum',
        'missing_months_policy' => 'ignore',
    ]);

    $month = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $monthlyGrade = MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month->id,
        'template_category_id' => $templateCategory->id,
        'category_key' => $categoryKey,
        'category' => $enteredLabel,
        'score' => 8,
        'max_score' => 10,
    ]);

    $result = $this->service->syncFromMonthlyGrade($monthlyGrade);

    expect($result->success)->toBeTrue();

    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $templateCategory->id)
        ->first();

    expect($mark)->not->toBeNull();
});

test('it preserves aggregation after monthly category key rename', function () {
    $academicYear = $this->academicYear;
    $term = Term::factory()->create(['academic_year_id' => $academicYear->id]);
    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $template = GradingTemplate::factory()->create();

    $templateCategory = TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'أعمال السنة',
        'mapping_type' => 'manual',
        'weight' => 10,
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 100,
        'pass_score' => 50,
        'counts_in_gpa' => true,
    ]);

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    $originalKey = 'homework';
    $originalLabel = 'واجبات';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $originalKey, 'label' => $originalLabel, 'max_score' => 10],
        ])]
    );

    $mapping = MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $originalKey,
        'template_category_id' => $templateCategory->id,
        'aggregation_rule' => 'sum',
        'missing_months_policy' => 'ignore',
    ]);

    $month = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $monthlyGrade = MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month->id,
        'template_category_id' => $templateCategory->id,
        'category_key' => $originalKey,
        'category' => $originalLabel,
        'score' => 8,
        'max_score' => 10,
    ]);

    $renamedKey = 'assignments';
    $renamedLabel = 'واجبات جديدة';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $renamedKey, 'label' => $renamedLabel, 'max_score' => 10],
        ])]
    );

    $mapping->update(['category_key' => $renamedKey]);

    $result = $this->service->syncFromMonthlyGrade($monthlyGrade);

    expect($result->success)->toBeTrue();

    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $templateCategory->id)
        ->first();

    expect($mark)->not->toBeNull();
});

test('it does not create student mark when mapping is missing', function () {
    $academicYear = $this->academicYear;
    $term = Term::factory()->create(['academic_year_id' => $academicYear->id]);
    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $template = GradingTemplate::factory()->create();

    $templateCategory = TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'واجبات',
        'mapping_type' => 'manual',
        'weight' => 10,
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 100,
        'pass_score' => 50,
        'counts_in_gpa' => true,
    ]);

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    $month = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $monthlyGrade = MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month->id,
        'category_key' => 'homework',
        'category' => 'واجبات',
        'score' => 8,
        'max_score' => 10,
    ]);

    $result = $this->service->syncFromMonthlyGrade($monthlyGrade);

    expect($result->success)->toBeTrue();

    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $templateCategory->id)
        ->first();

    expect($mark)->toBeNull();
});

test('it aggregates monthly grades across months and writes student mark', function () {
    $academicYear = $this->academicYear;
    $term = Term::factory()->create(['academic_year_id' => $academicYear->id]);
    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $template = GradingTemplate::factory()->create();
    $templateCategory = TemplateCategory::factory()->create([
        'grading_template_id' => $template->id,
        'name' => 'أعمال السنة',
        'mapping_type' => 'manual',
        'weight' => 10,
        'max_raw_score' => 10,
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 100,
        'pass_score' => 50,
        'counts_in_gpa' => true,
    ]);

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    $categoryKey = 'homework';
    $categoryLabel = 'واجبات';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYear->id],
        ['monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
            ['key' => $categoryKey, 'label' => $categoryLabel, 'max_score' => 10],
        ])]
    );

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYear->id,
        'term_id' => $term->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'category_key' => $categoryKey,
        'template_category_id' => $templateCategory->id,
        'aggregation_rule' => 'avg',
        'missing_months_policy' => 'ignore',
    ]);

    $month1 = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'سبتمبر',
        'start_date' => '2025-09-01',
        'end_date' => '2025-09-30',
        'order' => 1,
    ]);
    $month2 = GradebookMonth::factory()->create([
        'term_id' => $term->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'أكتوبر',
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
        'order' => 2,
    ]);

    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month1->id,
        'template_category_id' => $templateCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 8,
        'max_score' => 10,
    ]);
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month2->id,
        'template_category_id' => $templateCategory->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 6,
        'max_score' => 10,
    ]);

    $result = $this->service->syncFromMonthlyGrade(MonthlyGrade::first());

    expect($result->success)->toBeTrue();

    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $templateCategory->id)
        ->where('term_id', $term->id)
        ->first();

    expect($mark)->not->toBeNull();
    expect((float) $mark->raw_score)->toBe(7.0);
    expect((float) $mark->scaled_score)->toBe(7.0);
});
