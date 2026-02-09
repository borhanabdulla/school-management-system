<?php

namespace Tests\Feature\Livewire\Admin\Events;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Infrastructure\Context\AcademicContextService;
use App\Livewire\Admin\Events\EventManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class EventManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uses_db_active_year_when_cache_is_stale(): void
    {
        $staleYear = AcademicYear::factory()->create(['status' => 'pending']);
        $activeYear = AcademicYear::factory()->active()->create();

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, $staleYear, 3600);

        Livewire::test(EventManager::class)
            ->set('title', 'اختبار')
            ->set('start_date', '2025-01-01')
            ->set('end_date', '2025-01-02')
            ->set('type', 'holiday')
            ->set('is_holiday', true)
            ->set('description', '')
            ->call('save');

        $this->assertDatabaseHas('school_events', [
            'academic_year_id' => $activeYear->id,
            'title' => 'اختبار',
        ]);

        $this->assertDatabaseMissing('school_events', [
            'academic_year_id' => $staleYear->id,
            'title' => 'اختبار',
        ]);
    }
}
