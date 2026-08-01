<?php

namespace Tests\Feature\Domains\Academic\Grading;

use App\Domains\Academic\Attendance\Actions\RecordStudentAttendanceAction;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Models\Timetable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingEventIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_syncs_to_monthly_grade_and_student_mark()
    {
        // 1. Setup Data
        $term = Term::factory()->create([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        $month = GradebookMonth::factory()->create([
            'term_id' => $term->id,
            'academic_year_id' => $term->academic_year_id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'name' => 'Test Month',
        ]);

        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();

        $classSection = ClassSection::factory()->create([
            'academic_year_id' => $term->academic_year_id,
            'grade_id' => $grade->id,
        ]);

        $student = Student::factory()->create([
            'current_class_section_id' => $classSection->id
        ]);

        $subject = Subject::factory()->create();
        $courseOffering = CourseOffering::factory()->create([
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
            'term_id' => $term->id,
            'academic_year_id' => $term->academic_year_id,
        ]);

        // Setup Grading Config
        $template = GradingTemplate::factory()->create();
        $category = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'Attendance',
            'mapping_type' => 'attendance',
            'weight' => 10,
        ]);

        SubjectGradingConfig::updateOrCreate([
            'subject_id' => $subject->id,
            'grade_id' => $classSection->grade_id,
            'term_id' => $term->id,
        ], [
            'grading_template_id' => $template->id,
            'max_score' => 100,
            'pass_score' => 50,
            'counts_in_gpa' => true,
        ]);

        // Setup Gradebook Settings (Global)
        GradebookSettings::create([
            'academic_year_id' => $term->academic_year_id,
            'attendance_max_score' => 10,
            'attendance_deduct_after' => 0,
            'attendance_deduct_per_absence' => 1,
            'monthly_categories' => [
                ['name' => 'Attendance', 'is_attendance' => true, 'max_score' => 10]
            ]
        ]);

        MonthlyCategoryMapping::create([
            'academic_year_id' => $term->academic_year_id,
            'term_id' => $term->id,
            'grade_id' => $classSection->grade_id,
            'subject_id' => $subject->id,
            'template_category_id' => $category->id,
            'category_key' => 'attendance',
            'aggregation_rule' => 'sum',
            'missing_months_policy' => 'ignore',
        ]);

        // Setup Timetable
        $timeSlot = TimeSlot::factory()->create();
        $timetable = Timetable::factory()->create([
            'class_section_id' => $classSection->id,
            'course_offering_id' => $courseOffering->id,
            'time_slot_id' => $timeSlot->id,
            'term_id' => $term->id,
        ]);

        // 2. Execute Action (Record Attendance as Absent)
        $action = app(RecordStudentAttendanceAction::class);
        $date = now()->format('Y-m-d');

        $studentsData = [
            [
                'student_id' => $student->id,
                'status' => AttendanceStatus::ABSENT->value,
                'delay_minutes' => 0,
                'remarks' => 'Test',
            ]
        ];

        // We expect the event to be dispatched and the listener to run
        // Since we are in a test environment, the queue might be sync or fake.
        // We'll assume sync for this test or explicitly run it.

        $action->execute($timetable, $date, $studentsData, 1);

        $effectiveMonth = GradebookMonth::where('academic_year_id', $term->academic_year_id)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->firstOrFail();

        // 3. Verify Side Effects

        // A. Verify MonthlyGrade
        $this->assertDatabaseHas('monthly_grades', [
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'gradebook_month_id' => $effectiveMonth->id,
            'score' => 9, // 10 max - 1 deduction
        ]);

        // B. Verify StudentMark
        $this->assertDatabaseHas('student_marks', [
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'template_category_id' => $category->id,
            'raw_score' => 9,
            'scaled_score' => 9, // (9/10) * 10
        ]);
    }
}
