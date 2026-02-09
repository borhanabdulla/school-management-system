<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\AcademicYear\Actions\DeleteAcademicYearAction;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotDeletableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteYearReproductionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_deleting_pending_year_with_terms()
    {
        // 1. Create Pending Year
        $year = AcademicYear::factory()->create([
            'status' => \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Pending
        ]);

        // 2. Create Terms for it
        Term::factory()->count(2)->create([
            'academic_year_id' => $year->id
        ]);

        $this->assertCount(2, $year->terms);

        // 3. Try to delete using Action
        $this->expectException(YearNotDeletableException::class);
        app(DeleteAcademicYearAction::class)->execute($year->id);

        // 4. Verify deletion
        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
        $this->assertDatabaseHas('terms', ['academic_year_id' => $year->id]);
    }
}
