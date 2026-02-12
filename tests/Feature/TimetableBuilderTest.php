<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Livewire\Academic\TimetableBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TimetableBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_editing_active_year()
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active,
        ]);

        $component = Livewire::test(TimetableBuilder::class)
            ->set('selectedYearId', $year->id);

        $this->assertFalse($component->instance()->isReadOnly());
    }

    /** @test */
    public function it_allows_editing_pending_year()
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Pending,
        ]);

        $component = Livewire::test(TimetableBuilder::class)
            ->set('selectedYearId', $year->id);

        $this->assertFalse($component->instance()->isReadOnly());
    }

    /** @test */
    public function it_blocks_editing_closed_year()
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Closed,
        ]);

        $component = Livewire::test(TimetableBuilder::class)
            ->set('selectedYearId', $year->id);

        $this->assertTrue($component->instance()->isReadOnly());
    }

    /** @test */
    public function it_blocks_saving_without_term_selection()
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active,
        ]);

        Livewire::test(TimetableBuilder::class)
            ->set('selectedYearId', $year->id)
            ->set('selectedTermId', null)
            ->call('saveSession')
            ->assertDispatched('error');
    }
}
