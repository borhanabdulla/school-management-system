<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\AcademicYear\Actions\DeleteAcademicYearAction;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotDeletableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TraceDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trace_deletion_steps()
    {
        Log::info('STARTING TRACE DELETE TEST');

        // 1. Create Pending Year
        $year = AcademicYear::factory()->create([
            'status' => \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Pending,
            'name' => 'Trace Year'
        ]);
        Log::info('Year Created', ['id' => $year->id, 'status' => $year->status]);

        // 2. Create Terms
        Term::factory()->count(2)->create(['academic_year_id' => $year->id]);
        Log::info('Terms Created', ['count' => $year->terms()->count()]);

        // 3. Create Sections
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        ClassSection::factory()->count(1)->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'name' => 'Trace Section'
        ]);
        Log::info('Sections Created', ['count' => $year->sections()->count()]);

        // 4. Attempt Delete
        $this->expectException(YearNotDeletableException::class);
        Log::info('Attempting Delete Action...');
        app(DeleteAcademicYearAction::class)->execute($year->id);

        // 5. Verify
        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
        Log::info('Verification Passed');
    }
}
