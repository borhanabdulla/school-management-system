<?php

namespace Tests\Feature\Grading;

use Tests\TestCase;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Listeners\SyncMonthlyToStudentMark;
use App\Domains\Academic\Grading\Events\MonthlyGradeSaved;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MonthlyGradeSavedListenerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_listener_aggregates_monthly_grades_into_student_mark(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_year_id' => $year->id]);
        $grade = Grade::factory()->create();
        $subject = Subject::factory()->create();
        $classSection = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $offering = CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
        ]);

        $settings = GradebookSettings::create([
            'academic_year_id' => $year->id,
            'monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
                ['label' => 'واجبات', 'max_score' => 10, 'is_default' => true],
            ]),
            'attendance_deduct_after' => 3,
            'attendance_deduct_per_absence' => 0.5,
            'attendance_max_score' => 5,
            'allow_custom_categories' => true,
        ]);

        $categoryKey = $settings->monthly_categories[0]['key'];

        $month1 = GradebookMonth::factory()->forTerm($term)->create([
            'academic_year_id' => $year->id,
            'order' => 1,
        ]);
        $month2 = GradebookMonth::factory()->forTerm($term)->create([
            'academic_year_id' => $year->id,
            'order' => 2,
        ]);

        $config = SubjectGradingConfig::where('subject_id', $subject->id)
            ->where('grade_id', $grade->id)
            ->where('term_id', $term->id)
            ->with('template.categories')
            ->firstOrFail();

        $templateCategory = $config->template->categories->first();

        MonthlyCategoryMapping::create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'category_key' => $categoryKey,
            'template_category_id' => $templateCategory->id,
            'aggregation_rule' => 'sum',
            'missing_months_policy' => 'ignore',
        ]);

        $student = Student::factory()->create();

        MonthlyGrade::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
            'gradebook_month_id' => $month1->id,
            'category_key' => $categoryKey,
            'category' => 'واجبات',
            'score' => 8,
            'max_score' => 10,
        ]);

        $grade2 = MonthlyGrade::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
            'gradebook_month_id' => $month2->id,
            'category_key' => $categoryKey,
            'category' => 'واجبات',
            'score' => 6,
            'max_score' => 10,
        ]);

        app(SyncMonthlyToStudentMark::class)->handle(new MonthlyGradeSaved($grade2));

        $this->assertDatabaseHas('student_marks', [
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
            'template_category_id' => $templateCategory->id,
            'term_id' => $term->id,
            'raw_score' => 14,
            'scaled_score' => 35,
        ]);
    }
}
