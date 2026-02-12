<?php

namespace Tests\Feature;

use App\Models\User;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Livewire\Academic\ClassSectionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClassSectionManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_renders_without_errors()
    {
        // Setup data
        $year = AcademicYear::create(['name' => '2024-2025', 'start_date' => '2024-09-01', 'end_date' => '2025-06-30', 'status' => 'active']);
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create(['name' => 'Grade 1', 'educational_stage_id' => $stage->id, 'level_order' => 1]);

        Livewire::test(ClassSectionManager::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_can_filter_by_grade()
    {
        // Setup data
        $year = AcademicYear::create(['name' => '2024-2025', 'start_date' => '2024-09-01', 'end_date' => '2025-06-30', 'status' => 'active']);
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create(['name' => 'Grade 1', 'educational_stage_id' => $stage->id, 'level_order' => 1]);

        // This should trigger the query with the join and where clause
        Livewire::test(ClassSectionManager::class)
            ->set('filterGrade', $grade->id)
            ->assertStatus(200)
            ->assertSee('Grade 1');
    }

    /** @test */
    public function it_prevents_duplicate_section_name_in_same_grade_and_year()
    {
        $year = AcademicYear::create(['name' => '2024-2025', 'start_date' => '2024-09-01', 'end_date' => '2025-06-30', 'status' => 'active']);
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create(['name' => 'Grade 1', 'educational_stage_id' => $stage->id, 'level_order' => 1]);

        ClassSection::create([
            'name' => 'Section A',
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'max_capacity' => 30,
            'gender_type' => 'mixed',
            'is_active' => true,
        ]);

        Livewire::test(ClassSectionManager::class)
            ->set('form.academic_year_id', $year->id)
            ->set('form.grade_id', $grade->id)
            ->set('form.name', 'Section A')
            ->set('form.max_capacity', 30)
            ->set('form.gender_type', 'mixed')
            ->set('form.is_active', true)
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique']);
    }
}
