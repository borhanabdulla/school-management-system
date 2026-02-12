<?php

namespace Tests\Feature;

use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Grade\Models\Grade;
use App\Models\User;
use App\Livewire\Academic\StructureManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StructureManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_create_educational_stage()
    {
        Livewire::test(StructureManager::class)
            ->set('stageForm.name', 'Primary Stage')
            ->set('stageForm.rank', 1)
            ->set('stageForm.min_passing_percentage', 50)
            ->set('stageForm.grading_system', 'standard')
            ->call('saveStage')
            ->assertDispatched('notify', message: 'تم إنشاء المرحلة.');

        $this->assertDatabaseHas('educational_stages', [
            'name' => 'Primary Stage',
            'rank' => 1,
        ]);
    }

    /** @test */
    public function it_prevents_deleting_stage_with_grades()
    {
        $stage = EducationalStage::create(['name' => 'Stage With Grades', 'rank' => 1]);
        Grade::create(['educational_stage_id' => $stage->id, 'name' => 'Grade 1', 'level_order' => 1]);

        Livewire::test(StructureManager::class)
            ->call('deleteStage', $stage->id)
            ->assertDispatched('error', message: 'لا يمكن حذف المرحلة لأنها مرتبطة بصفوف.');

        $this->assertDatabaseHas('educational_stages', ['id' => $stage->id]);
    }

    /** @test */
    public function it_can_create_grade()
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);

        Livewire::test(StructureManager::class)
            ->set('gradeForm.educational_stage_id', $stage->id)
            ->set('gradeForm.name', 'Grade 1')
            ->set('gradeForm.level_order', 1)
            ->call('saveGrade')
            ->assertDispatched('notify', message: 'تم إنشاء الصف.');

        $this->assertDatabaseHas('grades', [
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
        ]);
    }

    /** @test */
    public function it_detects_circular_reference()
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $g1 = Grade::create(['educational_stage_id' => $stage->id, 'name' => 'G1', 'level_order' => 1]);
        $g2 = Grade::create(['educational_stage_id' => $stage->id, 'name' => 'G2', 'level_order' => 2, 'next_grade_id' => $g1->id]); // G2 -> G1

        // Try to make G1 -> G2 (Circular)
        Livewire::test(StructureManager::class)
            ->call('editGrade', $g1)
            ->set('gradeForm.next_grade_id', $g2->id)
            ->call('saveGrade')
            ->assertHasErrors(['gradeForm.next_grade_id']);
    }

    /** @test */
    public function it_prevents_duplicate_grade_name_in_same_stage()
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        Grade::create(['educational_stage_id' => $stage->id, 'name' => 'Grade 1', 'level_order' => 1]);

        Livewire::test(StructureManager::class)
            ->set('gradeForm.educational_stage_id', $stage->id)
            ->set('gradeForm.name', 'Grade 1')
            ->set('gradeForm.level_order', 2)
            ->call('saveGrade')
            ->assertHasErrors(['gradeForm.name' => 'unique']);
    }
}
