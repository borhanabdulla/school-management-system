<?php

namespace Tests\Unit\Domains\Academic\Grading\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Services\GradebookMonthService;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradebookMonthServiceTest extends TestCase
{
    use RefreshDatabase;

    private GradebookMonthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradebookMonthService();
    }

    public function test_it_generates_months_for_term()
    {
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'start_date' => '2023-09-01',
            'end_date' => '2023-12-15',
        ]);

        $this->service->generateForTerm($term);

        $months = GradebookMonth::where('term_id', $term->id)->orderBy('order')->get();

        $this->assertCount(4, $months);
        $this->assertEquals('سبتمبر', $months[0]->name);
        $this->assertEquals('أكتوبر', $months[1]->name);
        $this->assertEquals('نوفمبر', $months[2]->name);
        $this->assertEquals('ديسمبر', $months[3]->name);

        $this->assertEquals('2023-09-01', $months[0]->start_date->toDateString());
        $this->assertEquals('2023-09-30', $months[0]->end_date->toDateString());

        $this->assertEquals('2023-12-01', $months[3]->start_date->toDateString());
        $this->assertEquals('2023-12-15', $months[3]->end_date->toDateString());
    }

    public function test_it_updates_existing_months_when_term_dates_change()
    {
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'start_date' => '2023-09-01',
            'end_date' => '2023-10-30',
        ]);

        $this->service->generateForTerm($term);
        $this->assertCount(2, GradebookMonth::where('term_id', $term->id)->get());

        // Extend Term
        $term->update(['end_date' => '2023-11-30']);
        $this->service->generateForTerm($term);

        $months = GradebookMonth::where('term_id', $term->id)->get();
        $this->assertCount(3, $months);
        $this->assertNotNull($months->firstWhere('name', 'نوفمبر'));
    }

    public function test_it_deletes_stale_months_if_no_grades()
    {
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'start_date' => '2023-09-01',
            'end_date' => '2023-11-30',
        ]);

        $this->service->generateForTerm($term);
        $this->assertCount(3, GradebookMonth::where('term_id', $term->id)->get());

        // Shrink Term
        $term->update(['end_date' => '2023-10-30']);
        $this->service->generateForTerm($term);

        $months = GradebookMonth::where('term_id', $term->id)->get();
        $this->assertCount(2, $months);
        $this->assertNull($months->firstWhere('name', 'نوفمبر'));
    }

    public function test_it_does_not_delete_stale_months_if_they_have_grades()
    {
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'start_date' => '2023-09-01',
            'end_date' => '2023-11-30',
        ]);

        $this->service->generateForTerm($term);

        $november = GradebookMonth::where('term_id', $term->id)->where('name', 'نوفمبر')->first();

        // Simulate existing grade (mocking relation or creating dummy)
        // Since we don't want to rely on full factory chain for MonthlyGrade if complex, 
        // we can just mock the exists() check if we were using mocks, but here we use DB.
        // We'll try to create a dummy MonthlyGrade if possible, or just skip if too complex deps.
        // Assuming MonthlyGradeFactory exists and works.

        try {
            MonthlyGrade::factory()->create([
                'gradebook_month_id' => $november->id,
                // minimal required fields
            ]);
        } catch (\Exception $e) {
            // If factory fails due to dependencies, we might need to manually insert or skip
            // For now, let's assume it works or we manually insert.
            // Let's manually insert to be safe and minimal
            \Illuminate\Support\Facades\DB::table('monthly_grades')->insert([
                'gradebook_month_id' => $november->id,
                'student_id' => 1, // dummy
                'course_offering_id' => 1, // dummy
                'category' => 'test',
                'score' => 10,
                'max_score' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Shrink Term
        $term->update(['end_date' => '2023-10-30']);
        $this->service->generateForTerm($term);

        $months = GradebookMonth::where('term_id', $term->id)->get();
        // Should still be 3 because November has grades
        $this->assertCount(3, $months);
        $this->assertNotNull($months->firstWhere('name', 'نوفمبر'));
    }
}
