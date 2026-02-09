<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Shared\Models\User;
use App\Livewire\Academic\TermManager;
use App\Domains\Academic\AcademicYear\Actions\CreateAcademicYearAction;
use App\Domains\Academic\Term\Actions\ActivateTermAction;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Term\Exceptions\TermYearNotActiveException;
use App\Infrastructure\Exceptions\BusinessRuleException;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class TermActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_activate_term_manually()
    {
        // Create active academic year
        $year = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active',
        ]);

        // Create terms
        $t1 = Term::create(['academic_year_id' => $year->id, 'name' => 'T1', 'status' => 'active', 'order_index' => 1]);
        $t2 = Term::create(['academic_year_id' => $year->id, 'name' => 'T2', 'status' => 'pending', 'order_index' => 2]);

        // Call activateTerm on T2
        Livewire::test(TermManager::class)
            ->call('activateTerm', $t2->id);

        // Assert T2 is active and T1 is completed
        $this->assertEquals(\App\Domains\Academic\Term\Enums\TermStatus::Active, $t2->fresh()->status);
        $this->assertEquals(\App\Domains\Academic\Term\Enums\TermStatus::Completed, $t1->fresh()->status);
    }

    /** @test */
    public function it_does_not_auto_activate_terms_on_year_creation()
    {
        $data = [
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
            'terms' => [
                ['name' => 'Term 1', 'start_date' => '2025-09-01', 'end_date' => '2025-12-31', 'order_index' => 1],
                ['name' => 'Term 2', 'start_date' => '2026-01-01', 'end_date' => '2026-06-30', 'order_index' => 2],
            ]
        ];

        $action = app(CreateAcademicYearAction::class);
        $year = $action->execute(\App\Domains\Academic\AcademicYear\Data\AcademicYearData::fromArray($data));

        $this->assertEquals(\App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active, $year->fresh()->status);
        $this->assertEquals(\App\Domains\Academic\Term\Enums\TermStatus::Pending, $year->terms()->where('name', 'Term 1')->first()->status);
        $this->assertEquals(\App\Domains\Academic\Term\Enums\TermStatus::Pending, $year->terms()->where('name', 'Term 2')->first()->status);
    }

    /** @test */
    public function it_cannot_activate_a_completed_term()
    {
        $year = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active',
        ]);

        $term = Term::create([
            'academic_year_id' => $year->id,
            'name' => 'T1',
            'status' => 'completed',
            'order_index' => 1,
        ]);

        $this->expectException(BusinessRuleException::class);
        app(ActivateTermAction::class)->execute($term);
    }

    /** @test */
    public function it_rejects_term_activation_when_active_year_cache_is_stale()
    {
        Cache::flush();

        $yearA = AcademicYear::create([
            'name' => '2022-2023',
            'start_date' => '2022-09-01',
            'end_date' => '2023-06-30',
            'status' => AcademicYearStatus::Pending,
        ]);

        $yearB = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        $termA = Term::create([
            'academic_year_id' => $yearA->id,
            'name' => 'A-T1',
            'status' => TermStatus::Pending,
            'order_index' => 1,
        ]);

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, $yearA, 60 * 60 * 24);
        $this->assertEquals($yearA->id, Cache::get(AcademicContextService::CACHE_KEY_YEAR)->id);

        try {
            app(ActivateTermAction::class)->execute($termA);
            $this->fail('Expected TermYearNotActiveException to be thrown.');
        } catch (TermYearNotActiveException $e) {
            $this->assertEquals(TermStatus::Pending, $termA->fresh()->status);
            $this->assertEquals(AcademicYearStatus::Active, $yearB->fresh()->status);
        }
    }
}
