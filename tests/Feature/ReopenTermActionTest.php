<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Actions\ReopenTermAction;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Infrastructure\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReopenTermActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_reopens_completed_term_as_pending()
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active,
        ]);
        school()->invalidateYear();

        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending,
        ]);
        $term->update(['status' => TermStatus::Completed]);

        app(ReopenTermAction::class)->execute($term);

        $this->assertEquals(TermStatus::Pending, $term->fresh()->status);
    }

    /** @test */
    public function it_rejects_reopen_when_another_term_is_active()
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active,
        ]);
        school()->invalidateYear();

        $activeTerm = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Active,
        ]);

        $completedTerm = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending,
        ]);
        $completedTerm->update(['status' => TermStatus::Completed]);

        $this->expectException(BusinessRuleException::class);
        app(ReopenTermAction::class)->execute($completedTerm);

        $this->assertEquals(TermStatus::Active, $activeTerm->fresh()->status);
        $this->assertEquals(TermStatus::Completed, $completedTerm->fresh()->status);
    }
}
