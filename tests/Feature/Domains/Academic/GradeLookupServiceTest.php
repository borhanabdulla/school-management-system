<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Academic\Stage\Models\EducationalStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_grades_for_academic_year_with_stage_loaded(): void
    {
        $year = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active',
        ]);

        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create([
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
            'level_order' => 1,
        ]);

        ClassSection::create([
            'name' => 'A',
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $service = app(GradeLookupService::class);
        $grades = $service->getGradesByAcademicYear($year->id);

        $this->assertCount(1, $grades);
        $this->assertSame($grade->id, $grades->first()->id);
        $this->assertTrue($grades->first()->relationLoaded('stage'));
        $this->assertSame('Primary', $grades->first()->stage->name);
    }
}
