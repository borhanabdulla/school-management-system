<?php

namespace Tests\Feature;

use App\Domains\Shared\Models\User;
use App\Domains\Academic\Subject\Models\Subject;
use App\Livewire\Academic\SubjectManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubjectManagerTest extends TestCase
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
        Livewire::test(SubjectManager::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_can_create_subject()
    {
        Livewire::test(SubjectManager::class)
            ->set('form.name', 'Mathematics')
            ->set('form.code', 'MATH101')
            ->set('form.type', 'theory')
            ->call('saveSubject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subjects', [
            'name' => 'Mathematics',
            'code' => 'MATH101',
            'type' => 'theory',
        ]);
    }

    /** @test */
    public function it_can_edit_subject()
    {
        $subject = Subject::create([
            'name' => 'Physics',
            'code' => 'PHYS101',
            'type' => 'practical',
        ]);

        Livewire::test(SubjectManager::class)
            ->call('editSubject', $subject->id)
            ->assertSet('form.id', $subject->id)
            ->assertSet('form.name', 'Physics')
            ->set('form.name', 'Advanced Physics')
            ->call('saveSubject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Advanced Physics',
        ]);
    }
}
