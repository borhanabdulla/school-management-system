<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\AcademicYear\Actions\DeleteAcademicYearAction;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotDeletableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeleteYearWithAuxiliaryDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_deleting_pending_year_with_auxiliary_data()
    {
        // 1. Create Pending Year
        $year = AcademicYear::factory()->create([
            'status' => \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Pending
        ]);

        // 2. Create Terms
        Term::factory()->count(2)->create(['academic_year_id' => $year->id]);

        // 3. Create Sections
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        ClassSection::factory()->count(1)->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'name' => 'Aux Section'
        ]);

        // 4. Create Fee Structure (if table exists)
        if (Schema::hasTable('fee_structures')) {
            $feeType = \Illuminate\Support\Facades\DB::table('fee_types')->insertGetId([
                'name' => 'Test Fee Type',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::table('fee_structures')->insert([
                'academic_year_id' => $year->id,
                'fee_type_id' => $feeType,
                'grade_id' => $grade->id,
                'amount' => 1000,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 5. Create School Event (if table exists)
        if (Schema::hasTable('school_events')) {
            \Illuminate\Support\Facades\DB::table('school_events')->insert([
                'academic_year_id' => $year->id,
                'title' => 'Test Event',
                'start_date' => now(),
                'end_date' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 6. Attempt Delete
        $this->expectException(YearNotDeletableException::class);
        app(DeleteAcademicYearAction::class)->execute($year->id);

        // 7. Verify
        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
    }
}
