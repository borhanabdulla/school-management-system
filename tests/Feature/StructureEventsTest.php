<?php

namespace Tests\Feature;

use App\Domains\Academic\Events\StructureChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Domains\Shared\Models\User;

class StructureEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_dispatches_event_on_stage_creation()
    {
        Event::fake();

        $data = new \App\Domains\Academic\Stage\Data\StageData(
            name: 'New Stage',
            rank: 1,
            min_passing_percentage: 50.0,
            grading_system: 'standard'
        );

        app(\App\Domains\Academic\Stage\Actions\CreateStageAction::class)->execute($data);

        Event::assertDispatched(StructureChanged::class, function ($event) {
            return $event->type === StructureChanged::TYPE_STAGE &&
                $event->action === StructureChanged::ACTION_CREATED;
        });
    }

    /** @test */
    public function it_dispatches_event_on_grade_creation()
    {
        Event::fake();

        $stage = \App\Domains\Academic\Stage\Models\EducationalStage::factory()->create();
        $data = new \App\Domains\Academic\Grade\Data\GradeData(
            name: 'New Grade',
            level_order: 1,
            educational_stage_id: $stage->id,
            next_grade_id: null
        );

        app(\App\Domains\Academic\Grade\Actions\CreateGradeAction::class)->execute($data);

        Event::assertDispatched(StructureChanged::class, function ($event) {
            return $event->type === StructureChanged::TYPE_GRADE &&
                $event->action === StructureChanged::ACTION_CREATED;
        });
    }

    /** @test */
    public function it_dispatches_event_on_section_creation()
    {
        Event::fake();

        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        $year = \App\Domains\Academic\AcademicYear\Models\AcademicYear::factory()->create();

        $data = new \App\Domains\Academic\ClassSection\Data\ClassSectionData(
            name: 'New Section',
            grade_id: $grade->id,
            academic_year_id: $year->id,
            max_capacity: 30,
            gender_type: \App\Domains\Academic\ClassSection\Enums\SectionGenderType::Boys,
            is_active: true
        );

        app(\App\Domains\Academic\ClassSection\Actions\CreateClassSectionAction::class)->execute($data);

        Event::assertDispatched(StructureChanged::class, function ($event) {
            return $event->type === StructureChanged::TYPE_SECTION &&
                $event->action === StructureChanged::ACTION_CREATED;
        });
    }
}
