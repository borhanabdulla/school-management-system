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
use App\Domains\Academic\Grading\Services\GradingHealthGate;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GradingHealthGateMappingTest extends TestCase
{
    use DatabaseMigrations;

    public function test_health_gate_blocks_when_mappings_are_missing(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_year_id' => $year->id]);
        $grade = Grade::factory()->create();
        $subject = Subject::factory()->create();
        $classSection = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
        ]);

        GradebookSettings::create([
            'academic_year_id' => $year->id,
            'monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
                ['label' => 'واجبات', 'max_score' => 10, 'is_default' => true],
            ]),
            'attendance_deduct_after' => 3,
            'attendance_deduct_per_absence' => 0.5,
            'attendance_max_score' => 5,
            'allow_custom_categories' => true,
        ]);

        $this->expectException(InvalidOperationException::class);

        app(GradingHealthGate::class)->assertTermHealthy($term, 'معالجة النتائج');
    }
}
