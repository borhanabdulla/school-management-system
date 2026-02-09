<?php

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Events\AttendanceBatchSaved;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grading\Listeners\SyncAttendanceToMonthlyGrade;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\Timetable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance batch sync selects month by term_id', function () {
    $year = AcademicYear::factory()->create();
    $term1 = Term::factory()->create([
        'academic_year_id' => $year->id,
        'start_date' => '2025-09-01',
        'end_date' => '2025-12-31',
    ]);
    $term2 = Term::factory()->create([
        'academic_year_id' => $year->id,
        'start_date' => '2025-09-01',
        'end_date' => '2025-12-31',
    ]);

    $grade = Grade::factory()->create();
    $classSection = ClassSection::factory()->create([
        'grade_id' => $grade->id,
        'academic_year_id' => $year->id,
    ]);
    $subject = Subject::factory()->create();

    $courseOffering = CourseOffering::factory()->create([
        'academic_year_id' => $year->id,
        'term_id' => $term1->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
    ]);

    $config = SubjectGradingConfig::where('subject_id', $subject->id)
        ->where('grade_id', $classSection->grade_id)
        ->where('term_id', $term1->id)
        ->with('template.categories')
        ->firstOrFail();

    $attendanceCategory = $config->template
        ->categories
        ->first();

    $attendanceCategory->update(['mapping_type' => 'attendance']);

    $month1 = GradebookMonth::where('term_id', $term1->id)
        ->where('start_date', '<=', '2025-09-10')
        ->where('end_date', '>=', '2025-09-10')
        ->firstOrFail();

    $month2 = GradebookMonth::where('term_id', $term2->id)
        ->where('start_date', '<=', '2025-09-10')
        ->where('end_date', '>=', '2025-09-10')
        ->firstOrFail();

    $student = Student::factory()->create([
        'current_class_section_id' => $classSection->id,
    ]);

    Attendance::factory()
        ->forStudent($student)
        ->forClassSection($classSection)
        ->absent()
        ->forDate('2025-09-10')
        ->create([
            'term_id' => $term1->id,
            'academic_year_id' => $year->id,
        ]);

    $timetable = Timetable::factory()->create([
        'class_section_id' => $classSection->id,
        'course_offering_id' => $courseOffering->id,
        'term_id' => $term1->id,
    ]);

    $event = new AttendanceBatchSaved($timetable, '2025-09-10', [$student->id]);

    app(SyncAttendanceToMonthlyGrade::class)->handle($event);

    $this->assertDatabaseHas('monthly_grades', [
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month1->id,
    ]);

    $this->assertDatabaseMissing('monthly_grades', [
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $month2->id,
    ]);
});
