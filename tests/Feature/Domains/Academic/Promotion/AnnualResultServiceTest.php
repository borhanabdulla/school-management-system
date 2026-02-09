<?php

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Promotion\Services\AnnualResultService;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Results\Enums\ResultDecision;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('annual result passes when total across terms meets combined pass score', function () {
    $year = AcademicYear::factory()->create();
    $term1 = Term::factory()->create([
        'academic_year_id' => $year->id,
        'order_index' => 1,
    ]);
    $term2 = Term::factory()->create([
        'academic_year_id' => $year->id,
        'order_index' => 2,
    ]);

    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $year->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $year->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'term_id' => $term1->id,
    ]);

    $student = Student::factory()->create([
        'current_grade_id' => $grade->id,
        'current_class_section_id' => $classSection->id,
    ]);

    StudentEnrollment::create([
        'student_id' => $student->id,
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'class_section_id' => $classSection->id,
        'enrollment_date' => now(),
        'enrollment_type' => 'new',
        'status' => \App\Domains\Academic\Student\Enums\EnrollmentStatus::Active,
    ]);

    $template = GradingTemplate::factory()->create();

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term1->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 50,
        'pass_score' => 25,
        'counts_in_gpa' => true,
    ]);

    SubjectGradingConfig::updateOrCreate([
        'subject_id' => $subject->id,
        'grade_id' => $grade->id,
        'term_id' => $term2->id,
    ], [
        'grading_template_id' => $template->id,
        'max_score' => 50,
        'pass_score' => 25,
        'counts_in_gpa' => true,
    ]);

    TermResult::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'term_id' => $term1->id,
        'coursework_score' => 23,
        'exam_score' => 0,
        'total_score' => 23,
        'max_score' => 50,
        'percentage' => 46,
        'grade_letter' => 'D',
        'is_passed' => false,
        'calculated_at' => now(),
    ]);

    TermResult::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'term_id' => $term2->id,
        'coursework_score' => 40,
        'exam_score' => 0,
        'total_score' => 40,
        'max_score' => 50,
        'percentage' => 80,
        'grade_letter' => 'B',
        'is_passed' => true,
        'calculated_at' => now(),
    ]);

    $service = app(AnnualResultService::class);
    $service->aggregateTermResults($year);
    $service->calculateDecisions($year);

    $annual = AnnualResult::where('student_id', $student->id)
        ->where('academic_year_id', $year->id)
        ->firstOrFail();

    expect((float) $annual->annual_total)->toBe(31.5);
    expect((float) $annual->annual_max)->toBe(50.0);
    expect($annual->failed_count)->toBe(1);
    expect($annual->decision)->toBe(ResultDecision::Conditional);
});
