<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\AcademicYear\Actions\DeleteAcademicYearAction;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotDeletableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteYearWithSectionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_deleting_pending_year_with_sections_but_no_students()
    {
        // 1. Create Pending Year
        $year = AcademicYear::factory()->create([
            'status' => \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Pending
        ]);

        // 2. Create Terms
        Term::factory()->count(2)->create(['academic_year_id' => $year->id]);

        // 3. Create Sections (but no students)
        // We need a Grade first
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        ClassSection::factory()->count(3)
            ->sequence(
                ['name' => 'Section A'],
                ['name' => 'Section B'],
                ['name' => 'Section C']
            )
            ->create([
                'academic_year_id' => $year->id,
                'grade_id' => $grade->id
            ]);

        $this->assertCount(3, $year->sections);

        // 4. Try to delete
        $this->expectException(YearNotDeletableException::class);
        app(DeleteAcademicYearAction::class)->execute($year->id);

        // 5. Verify deletion
        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
        $this->assertDatabaseHas('class_sections', ['academic_year_id' => $year->id]);
    }
}
