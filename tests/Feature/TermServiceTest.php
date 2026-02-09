<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Services\TermService;
use App\Infrastructure\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TermServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rejects_creating_term_as_active()
    {
        $year = AcademicYear::factory()->create();

        $this->expectException(BusinessRuleException::class);

        app(TermService::class)->createTerm([
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => $year->start_date->format('Y-m-d'),
            'end_date' => $year->start_date->copy()->addMonths(3)->format('Y-m-d'),
            'order_index' => 1,
            'status' => TermStatus::Active->value,
        ]);
    }

    /** @test */
    public function it_rejects_deleting_active_term()
    {
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Active,
        ]);

        $this->expectException(ValidationException::class);

        app(TermService::class)->deleteTerm($term);
    }
}
