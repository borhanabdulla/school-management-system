<?php

use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->lookupService = app(\App\Domains\Academic\Grading\Services\GradingLookupService::class);
    $this->service = new GradingCalculatorService($this->lookupService);

    // Setup basic data
    // Create one academic year explicitly to prevent multiple random creations colliding on constraints
    $this->academicYear = AcademicYear::factory()->create();

    $this->student = Student::factory()->create();
    $this->courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $this->academicYear->id,
    ]);

    $this->template = GradingTemplate::factory()->create([
        'academic_year_id' => $this->academicYear->id, // Ensure template is in same year
        'total_max_score' => 100,
        'pass_score' => 50,
    ]);

    $this->loadTemplate = function (): void {
        $this->template->load(['categories' => fn($q) => $q->with('children')]);
    };

    $this->marksFor = function ($student, $courseOffering) {
        return StudentMark::where('student_id', $student->id)
            ->where(function ($query) use ($courseOffering) {
                $query->where('course_offering_id', $courseOffering->id)
                    ->orWhereHas('assessment', fn($q) => $q->where('course_offering_id', $courseOffering->id));
            })
            ->with(['category', 'assessment.category'])
            ->get();
    };
});

test('it calculates simple category grade correctly', function () {
    // Arrange
    // Category: Exam (Weight 20%)
    $category = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'name' => 'Exam',
        'weight' => 20,
        'max_raw_score' => 100,
        'parent_id' => null,
    ]);

    // Assessment: Midterm (Max 100)
    $assessment = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 100,
    ]);

    // Student Mark: 80/100
    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $assessment->id,
        'raw_score' => 80, // 80%
    ]);

    // Act
    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $this->courseOffering);

    $result = $this->service->calculateStudentGrade(
        $this->student,
        $this->courseOffering,
        $this->template,
        $marks
    );

    // Assert
    // Weighted Score = 80% of 20 = 16
    expect($result['total'])->toBe(16.0);
    expect($result['max'])->toBe(20.0);
    expect($result['percentage'])->toBe(80.0);
});

test('it calculates nested categories correctly', function () {
    // Arrange
    // Parent: Coursework (Weight 40%)
    $parent = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'name' => 'Coursework',
        'weight' => 40,
        'parent_id' => null,
    ]);

    // Child 1: Homework (Weight 50% of Parent) -> Effective 20% of Total
    $child1 = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'parent_id' => $parent->id,
        'name' => 'Homework',
        'weight' => 50,
    ]);

    // Child 2: Quiz (Weight 50% of Parent) -> Effective 20% of Total
    $child2 = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'parent_id' => $parent->id,
        'name' => 'Quiz',
        'weight' => 50,
    ]);

    // Assessment for Homework: 10/10
    $hw = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $child1->id,
        'max_score' => 10,
    ]);
    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $hw->id,
        'raw_score' => 10,
    ]);

    // Assessment for Quiz: 5/10
    $quiz = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $child2->id,
        'max_score' => 10,
    ]);
    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $quiz->id,
        'raw_score' => 5,
    ]);

    // Act
    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $this->courseOffering);

    $result = $this->service->calculateStudentGrade(
        $this->student,
        $this->courseOffering,
        $this->template,
        $marks
    );

    // Assert
    // Homework: 100% -> contributes 50 points to parent
    // Quiz: 50% -> contributes 25 points to parent
    // Parent Total: 75 points (out of 100 base for parent) -> 75%
    // Weighted Total: 75% of 40 = 30

    expect($result['total'])->toBe(30.0);
    expect($result['max'])->toBe(40.0);
    expect($result['percentage'])->toBe(75.0);
});

test('it handles missing marks as zero', function () {
    // Arrange
    $category = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'weight' => 20,
        'parent_id' => null,
    ]);

    $assessment = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 100,
    ]);

    // No marks created

    // Act
    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $this->courseOffering);

    $result = $this->service->calculateStudentGrade(
        $this->student,
        $this->courseOffering,
        $this->template,
        $marks
    );

    // Assert
    expect($result['total'])->toBe(0.0);
});

test('it handles multiple assessments in same category', function () {
    // Arrange
    $category = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'weight' => 50,
        'parent_id' => null,
    ]);

    // Assessment 1: 10/10
    $a1 = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 10,
    ]);
    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $a1->id,
        'raw_score' => 10,
    ]);

    // Assessment 2: 5/10
    $a2 = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 10,
    ]);
    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $a2->id,
        'raw_score' => 5,
    ]);

    // Act
    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $this->courseOffering);

    $result = $this->service->calculateStudentGrade(
        $this->student,
        $this->courseOffering,
        $this->template,
        $marks
    );

    // Assert
    // Total Score: 15 / 20 = 75%
    // Weighted: 75% of 50 = 37.5
    expect($result['total'])->toBe(37.5);
});

test('it uses subject grading config for pass decision', function () {
    $academicYear = AcademicYear::factory()->create();
    $term = Term::factory()->create(['academic_year_id' => $academicYear->id]);
    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $academicYear->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'term_id' => $term->id,
    ]);

    $config = SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term->id,
    ], [
        'grading_template_id' => $this->template->id,
        'max_score' => 100,
        'pass_score' => 70,
        'counts_in_gpa' => true,
    ]);

    $category = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'name' => 'Exam',
        'weight' => 100,
        'parent_id' => null,
    ]);

    $assessment = Assessment::factory()->create([
        'course_offering_id' => $courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 100,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $assessment->id,
        'raw_score' => 60,
    ]);

    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $courseOffering);

    $result = $this->service->calculateStudentGrade(
        $this->student,
        $courseOffering,
        $this->template,
        $marks,
        (float) $config->pass_score
    );

    expect($result['percentage'])->toBe(60.0);
    expect($result['passed'])->toBeFalse();
});

test('it applies rounding rules from template', function () {
    $this->template->update([
        'rounding_rule' => 'up',
        'rounding_precision' => 0,
    ]);

    $category = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'name' => 'Exam',
        'weight' => 100,
        'parent_id' => null,
    ]);

    $assessment = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 100,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $assessment->id,
        'raw_score' => 83.2,
    ]);

    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $this->courseOffering);

    $result = $this->service->calculateStudentGrade(
        $this->student,
        $this->courseOffering,
        $this->template,
        $marks
    );

    expect($result['percentage'])->toBe(84.0);
    expect($result['score'])->toBe(84.0);
});

test('it respects max raw score for manual categories', function () {
    $category = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'name' => 'Coursework',
        'weight' => 100,
        'max_raw_score' => 50,
        'parent_id' => null,
    ]);

    $assessment1 = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 40,
    ]);
    $assessment2 = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 40,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $assessment1->id,
        'raw_score' => 40,
    ]);
    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $assessment2->id,
        'raw_score' => 40,
    ]);

    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $this->courseOffering);

    $result = $this->service->calculateStudentGrade(
        $this->student,
        $this->courseOffering,
        $this->template,
        $marks
    );

    expect($result['percentage'])->toBe(100.0);
    expect($result['score'])->toBe(100.0);
});

test('it calculates coursework from student marks and excludes final exam flags', function () {
    $finalCategory = TemplateCategory::factory()->create([
        'is_final_exam' => true,
    ]);

    $courseworkCategory = TemplateCategory::factory()->create([
        'is_final_exam' => false,
    ]);

    $assessment = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $courseworkCategory->id,
        'max_score' => 10,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $finalCategory->id,
        'scaled_score' => 30,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $courseworkCategory->id,
        'scaled_score' => 20,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'course_offering_id' => $this->courseOffering->id,
        'assessment_id' => $assessment->id,
        'scaled_score' => 15,
    ]);

    $marks = StudentMark::with(['category', 'assessment.category'])->get();

    $result = $this->service->calculateCourseworkScoreFromStudentMarks($marks);

    expect($result)->toBe(35.0);
});

test('it does not use name matching for final exams', function () {
    $namedFinalButNotFlagged = TemplateCategory::factory()->create([
        'name' => 'Final Exam',
        'is_final_exam' => false,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $namedFinalButNotFlagged->id,
        'scaled_score' => 12,
    ]);

    $marks = StudentMark::with(['category', 'assessment.category'])->get();

    $result = $this->service->calculateCourseworkScoreFromStudentMarks($marks);

    expect($result)->toBe(12.0);
});

test('it fails when a mark is missing category relation', function () {
    $mark = StudentMark::factory()->make([
        'scaled_score' => 10,
    ]);

    $mark->setRelation('category', null);
    $mark->setRelation('assessment', null);

    $marks = collect([$mark]);

    expect(fn() => $this->service->calculateCourseworkScoreFromStudentMarks($marks))
        ->toThrow(\RuntimeException::class, 'StudentMark missing category relation.');
});

test('it flags threshold failures for required categories', function () {
    $category = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'name' => 'Final Exam',
        'weight' => 100,
        'pass_required' => true,
        'pass_threshold' => 50,
        'parent_id' => null,
    ]);

    $assessment = Assessment::factory()->create([
        'course_offering_id' => $this->courseOffering->id,
        'template_category_id' => $category->id,
        'max_score' => 100,
    ]);

    StudentMark::factory()->create([
        'student_id' => $this->student->id,
        'assessment_id' => $assessment->id,
        'raw_score' => 40,
    ]);

    ($this->loadTemplate)();
    $marks = ($this->marksFor)($this->student, $this->courseOffering);

    $thresholds = $this->service->evaluateCategoryThresholds($this->template, $marks);

    expect($thresholds['failed'])->toBeTrue();
    expect($thresholds['failures'])->toHaveCount(1);
    expect($thresholds['failures'][0]['category_id'])->toBe($category->id);
});

test('it uses final exam score for final exam thresholds', function () {
    $finalCategory = TemplateCategory::factory()->create([
        'grading_template_id' => $this->template->id,
        'name' => 'Final Exam',
        'weight' => 60,
        'pass_required' => true,
        'pass_threshold' => 50,
        'is_final_exam' => true,
        'parent_id' => null,
    ]);

    ($this->loadTemplate)();
    $marks = collect();

    $thresholds = $this->service->evaluateCategoryThresholds($this->template, $marks, 30.0);

    expect($thresholds['failed'])->toBeTrue();
    expect($thresholds['failures'])->toHaveCount(1);
    expect($thresholds['failures'][0]['category_id'])->toBe($finalCategory->id);
    expect($thresholds['missing_thresholds'])->toBeEmpty();
});
