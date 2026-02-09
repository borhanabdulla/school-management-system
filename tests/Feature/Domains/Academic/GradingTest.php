<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Events\MonthlyGradeSaved;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GradingTest extends TestCase
{
    use RefreshDatabase;

    // ============================================
    // اختبارات الأشهر الدراسية
    // ============================================

    /** @test */
    public function can_create_gradebook_month(): void
    {
        $term = Term::factory()->create([
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-31',
        ]);

        $month = GradebookMonth::create([
            'term_id' => $term->id,
            'name' => 'سبتمبر',
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-30',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('gradebook_months', [
            'id' => $month->id,
            'name' => 'سبتمبر',
        ]);
    }

    /** @test */
    public function can_generate_months_for_term(): void
    {
        $term = Term::factory()->create([
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-31',
        ]);

        GradebookMonth::generateForTerm($term);

        // يجب أن يكون هناك 4 أشهر (سبتمبر، أكتوبر، نوفمبر، ديسمبر)
        $this->assertEquals(4, GradebookMonth::where('term_id', $term->id)->count());
    }

    /** @test */
    public function gradebook_month_belongs_to_term(): void
    {
        $term = Term::factory()->create();
        $month = GradebookMonth::factory()->forTerm($term)->create();

        $this->assertEquals($term->id, $month->term->id);
    }

    // ============================================
    // اختبارات الدرجات الشهرية
    // ============================================

    /** @test */
    public function can_create_monthly_grade(): void
    {
        $student = Student::factory()->create();
        $courseOffering = CourseOffering::factory()->create();
        $month = GradebookMonth::factory()->create();

        $grade = MonthlyGrade::create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'gradebook_month_id' => $month->id,
            'category' => 'تحريري',
            'score' => 8.5,
            'max_score' => 10,
        ]);

        $this->assertDatabaseHas('monthly_grades', [
            'id' => $grade->id,
            'score' => 8.5,
            'category' => 'تحريري',
        ]);
    }

    /** @test */
    public function monthly_grade_dispatches_saved_event(): void
    {
        Event::fake([MonthlyGradeSaved::class]);

        MonthlyGrade::factory()->create();

        Event::assertDispatched(MonthlyGradeSaved::class);
    }

    /** @test */
    public function monthly_grade_belongs_to_student(): void
    {
        $student = Student::factory()->create();
        $grade = MonthlyGrade::factory()->forStudent($student)->create();

        $this->assertEquals($student->id, $grade->student->id);
    }

    /** @test */
    public function monthly_grade_belongs_to_course_offering(): void
    {
        $courseOffering = CourseOffering::factory()->create();
        $grade = MonthlyGrade::factory()->forCourseOffering($courseOffering)->create();

        $this->assertEquals($courseOffering->id, $grade->courseOffering->id);
    }

    /** @test */
    public function monthly_grade_belongs_to_month(): void
    {
        $month = GradebookMonth::factory()->create();
        $grade = MonthlyGrade::factory()->forMonth($month)->create();

        $this->assertEquals($month->id, $grade->month->id);
    }

    // ============================================
    // اختبارات فئات الدرجات (من الـ Factory)
    // ============================================

    /** @test */
    public function monthly_grade_factory_creates_valid_record(): void
    {
        $grade = MonthlyGrade::factory()->create();

        $this->assertDatabaseHas('monthly_grades', ['id' => $grade->id]);
        $this->assertNotNull($grade->student_id);
        $this->assertNotNull($grade->course_offering_id);
        $this->assertNotNull($grade->gradebook_month_id);
    }

    /** @test */
    public function monthly_grade_written_state_has_correct_category(): void
    {
        $grade = MonthlyGrade::factory()->written()->create();

        $this->assertEquals('تحريري', $grade->category);
        $this->assertEquals(30, $grade->max_score);
    }

    /** @test */
    public function monthly_grade_oral_state_has_correct_category(): void
    {
        $grade = MonthlyGrade::factory()->oral()->create();

        $this->assertEquals('شفهي', $grade->category);
        $this->assertEquals(10, $grade->max_score);
    }

    /** @test */
    public function monthly_grade_homework_state_has_correct_category(): void
    {
        $grade = MonthlyGrade::factory()->homework()->create();

        $this->assertEquals('واجبات', $grade->category);
    }

    /** @test */
    public function monthly_grade_excellent_gives_high_percentage(): void
    {
        $grade = MonthlyGrade::factory()->excellent()->create();
        $percentage = ($grade->score / $grade->max_score) * 100;

        $this->assertGreaterThanOrEqual(90, $percentage);
    }

    /** @test */
    public function monthly_grade_failing_gives_low_percentage(): void
    {
        $grade = MonthlyGrade::factory()->failing()->create();
        $percentage = ($grade->score / $grade->max_score) * 100;

        $this->assertLessThan(50, $percentage);
    }

    // ============================================
    // اختبارات قوالب التقييم
    // ============================================

    /** @test */
    public function can_create_grading_template(): void
    {
        $academicYear = AcademicYear::factory()->create();

        $template = GradingTemplate::create([
            'name' => 'قالب ابتدائي',
            'total_max_score' => 100,
            'pass_score' => 50,
            'rounding_rule' => 'nearest_integer',
            'rounding_precision' => 2,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->assertDatabaseHas('grading_templates', [
            'id' => $template->id,
            'name' => 'قالب ابتدائي',
        ]);
    }

    /** @test */
    public function grading_template_can_have_categories(): void
    {
        $template = GradingTemplate::factory()->create();

        TemplateCategory::factory()->count(3)->create([
            'grading_template_id' => $template->id,
        ]);

        $this->assertCount(3, $template->categories);
    }

    /** @test */
    public function template_category_can_have_children(): void
    {
        $template = GradingTemplate::factory()->create();

        $parent = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'name' => 'أعمال السنة',
            'weight' => 40,
        ]);

        $child1 = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'parent_id' => $parent->id,
            'name' => 'التحريري',
            'weight' => 60,
        ]);

        $child2 = TemplateCategory::factory()->create([
            'grading_template_id' => $template->id,
            'parent_id' => $parent->id,
            'name' => 'الشفهي',
            'weight' => 40,
        ]);

        $parent->refresh();
        $this->assertCount(2, $parent->children);
    }

    // ============================================
    // اختبارات ترابط الدرجات
    // ============================================

    /** @test */
    public function unique_grade_per_student_course_month_category(): void
    {
        $student = Student::factory()->create();
        $courseOffering = CourseOffering::factory()->create();
        $month = GradebookMonth::factory()->create();

        MonthlyGrade::factory()
            ->forStudent($student)
            ->forCourseOffering($courseOffering)
            ->forMonth($month)
            ->create([
                'category' => 'تحريري',
                'category_key' => \App\Domains\Academic\Grading\Models\GradebookSettings::generateCategoryKey('تحريري'),
            ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        MonthlyGrade::factory()
            ->forStudent($student)
            ->forCourseOffering($courseOffering)
            ->forMonth($month)
            ->create([
                'category' => 'تحريري',
                'category_key' => \App\Domains\Academic\Grading\Models\GradebookSettings::generateCategoryKey('تحريري'),
            ]);
    }

    /** @test */
    public function same_student_can_have_different_category_grades(): void
    {
        $student = Student::factory()->create();
        $courseOffering = CourseOffering::factory()->create();
        $month = GradebookMonth::factory()->create();

        MonthlyGrade::factory()
            ->forStudent($student)
            ->forCourseOffering($courseOffering)
            ->forMonth($month)
            ->create(['category' => 'تحريري']);

        $grade2 = MonthlyGrade::factory()
            ->forStudent($student)
            ->forCourseOffering($courseOffering)
            ->forMonth($month)
            ->create(['category' => 'شفهي']);

        $this->assertDatabaseCount('monthly_grades', 2);
    }

    /** @test */
    public function student_grades_across_multiple_months(): void
    {
        $student = Student::factory()->create();
        $courseOffering = CourseOffering::factory()->create();
        $term = Term::factory()->create([
            'start_date' => '2025-09-01',
            'end_date' => '2025-11-30',
        ]);

        GradebookMonth::generateForTerm($term);
        $months = GradebookMonth::where('term_id', $term->id)->get();

        foreach ($months as $month) {
            MonthlyGrade::factory()
                ->forStudent($student)
                ->forCourseOffering($courseOffering)
                ->forMonth($month)
                ->written()
                ->create();
        }

        $studentGrades = MonthlyGrade::where('student_id', $student->id)
            ->where('course_offering_id', $courseOffering->id)
            ->get();

        $this->assertCount(3, $studentGrades);
    }
}
