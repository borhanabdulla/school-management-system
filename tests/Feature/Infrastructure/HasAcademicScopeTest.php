<?php

namespace Tests\Feature\Infrastructure;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Calendar\Models\SchoolEvent;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HasAcademicScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_autofills_academic_year_from_db_when_cache_is_stale(): void
    {
        $staleYear = AcademicYear::factory()->create(['status' => 'pending']);
        $activeYear = AcademicYear::factory()->active()->create();

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, $staleYear, 3600);

        $event = SchoolEvent::create([
            'title' => 'اختبار',
            'description' => null,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-02',
            'type' => 'holiday',
            'is_holiday' => true,
        ]);

        $this->assertSame($activeYear->id, $event->academic_year_id);
    }
}
