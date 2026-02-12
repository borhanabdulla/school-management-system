<?php

namespace Tests\Feature\Domains\Academic\Grading;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Actions\CalculateTermGradesAction;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Results\Models\TermResultFailure;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateTermGradesScaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_term_grades_uses_grading_scale_for_grade_letter(): void
    {
        SystemSetting::set('grading.scale', [
            ['min' => 0, 'max' => 59, 'grade' => 'F'],
            ['min' => 59, 'max' => 79, 'grade' => 'C'],
            ['min' => 79, 'max' => 100, 'grade' => 'A'],
        ], 'grading', 'json');

        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->create(['academic_year_id' => $year->id]);
        $classSection = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
        ]);
        $subject = Subject::factory()->create();

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
        ]);

        $student = Student::factory()->create([
            'current_class_section_id' => $classSection->id,
        ]);
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $classSection->grade_id,
            'class_section_id' => $classSection->id,
        ]);

        $config = SubjectGradingConfig::where('subject_id', $subject->id)
            ->where('grade_id', $classSection->grade_id)
            ->where('term_id', $term->id)
            ->with('template.categories')
            ->firstOrFail();

        $category = $config->template
            ->categories
            ->firstWhere('is_final_exam', false);

        $this->assertNotNull($category);

        $monthlyCategories = GradebookSettings::normalizeMonthlyCategories([
            ['label' => 'واجبات', 'max_score' => 10, 'is_default' => true],
        ]);

        GradebookSettings::create([
            'academic_year_id' => $year->id,
            'monthly_categories' => $monthlyCategories,
            'attendance_deduct_after' => 3,
            'attendance_deduct_per_absence' => 0.5,
            'attendance_max_score' => 5,
            'allow_custom_categories' => true,
        ]);

        foreach ($monthlyCategories as $monthlyCategory) {
            MonthlyCategoryMapping::create([
                'academic_year_id' => $year->id,
                'term_id' => $term->id,
                'grade_id' => $classSection->grade_id,
                'subject_id' => $subject->id,
                'category_key' => $monthlyCategory['key'],
                'template_category_id' => $category->id,
                'aggregation_rule' => 'sum',
                'missing_months_policy' => 'ignore',
            ]);
        }

        StudentMark::create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'template_category_id' => $category->id,
            'raw_score' => 80,
            'scaled_score' => 80,
        ]);

        app(CalculateTermGradesAction::class)->execute($classSection, $term);

        $result = TermResult::where('student_id', $student->id)
            ->where('course_offering_id', $courseOffering->id)
            ->where('term_id', $term->id)
            ->firstOrFail();

        $this->assertSame('A', $result->grade_letter);
    }

    public function test_calculate_term_grades_is_blocked_when_health_gate_fails(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->create(['academic_year_id' => $year->id]);
        $classSection = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
        ]);
        $subject = Subject::factory()->create();

        CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
        ]);

        SubjectGradingConfig::where('subject_id', $subject->id)
            ->where('grade_id', $classSection->grade_id)
            ->where('term_id', $term->id)
            ->delete();

        $this->expectException(InvalidOperationException::class);

        app(CalculateTermGradesAction::class)->execute($classSection, $term);
    }

    public function test_calculate_term_grades_records_threshold_failures(): void
    {
        SystemSetting::set('grading.scale', [
            ['min' => 0, 'max' => 59, 'grade' => 'F'],
            ['min' => 59, 'max' => 79, 'grade' => 'C'],
            ['min' => 79, 'max' => 100, 'grade' => 'A'],
        ], 'grading', 'json');

        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->create(['academic_year_id' => $year->id]);
        $classSection = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
        ]);
        $subject = Subject::factory()->create();

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
        ]);

        $template = GradingTemplate::factory()->create();
        $courseworkCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'أعمال السنة',
            'weight' => 60,
            'max_raw_score' => 100,
            'pass_required' => true,
            'pass_threshold' => 70,
            'is_final_exam' => false,
        ]);
        TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'الاختبار النهائي',
            'weight' => 40,
            'max_raw_score' => 40,
            'pass_required' => false,
            'is_final_exam' => true,
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

        $monthlyCategories = GradebookSettings::normalizeMonthlyCategories([
            ['label' => 'واجبات', 'max_score' => 10, 'is_default' => true],
        ]);
        GradebookSettings::updateOrCreate(
            ['academic_year_id' => $year->id],
            ['monthly_categories' => $monthlyCategories]
        );

        MonthlyCategoryMapping::updateOrCreate([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'grade_id' => $classSection->grade_id,
            'subject_id' => $subject->id,
            'category_key' => $monthlyCategories[0]['key'],
        ], [
            'template_category_id' => $courseworkCategory->id,
            'aggregation_rule' => 'sum',
            'missing_months_policy' => 'ignore',
        ]);

        $student = Student::factory()->create([
            'current_class_section_id' => $classSection->id,
        ]);
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $classSection->grade_id,
            'class_section_id' => $classSection->id,
        ]);

        StudentMark::create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'template_category_id' => $courseworkCategory->id,
            'raw_score' => 50,
            'scaled_score' => 50,
        ]);

        app(CalculateTermGradesAction::class)->execute($classSection, $term);

        $result = TermResult::where('student_id', $student->id)
            ->where('course_offering_id', $courseOffering->id)
            ->where('term_id', $term->id)
            ->firstOrFail();

        $failure = TermResultFailure::where('term_result_id', $result->id)
            ->where('template_category_id', $courseworkCategory->id)
            ->first();

        $this->assertNotNull($failure);
    }
}
