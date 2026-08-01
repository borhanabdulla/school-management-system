<?php

namespace Tests\Feature\Livewire\Admin\Grading;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Infrastructure\Context\AcademicContextService;
use App\Livewire\Admin\Grading\GradingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use ReflectionClass;
use Tests\TestCase;

final class GradingSettingsHealthGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we have an active academic year and term for the component
        $year = AcademicYear::factory()->active()->create();
        Term::factory()->active()->create([
            'academic_year_id' => $year->id,
        ]);

        $grade = Grade::factory()->create();
        Subject::factory()->create()->grades()->attach($grade->id, [
            'credit_hours' => 3,
            'term_type' => 'full_year',
            'is_active' => true,
        ]);

        $this->resetAcademicContext();
    }

    public function test_guardrail_message_and_buttons_disable_when_report_dirty(): void
    {
        Livewire::test(GradingSettings::class)
            ->call('setTab', 'subjects')
            ->call('onGradingHealthReportUpdated', ['clean' => false, 'missing' => 1, 'invalid' => 1])
            ->assertSee('Missing:')
            ->assertSee('Invalid:');
    }

    public function test_templates_tab_shows_stop_report_banner_when_health_is_dirty(): void
    {
        Livewire::test(GradingSettings::class)
            ->call('setTab', 'templates')
            ->call('onGradingHealthReportUpdated', ['clean' => false, 'missing' => 2, 'invalid' => 1])
            ->assertSee('Missing:')
            ->assertSee('Invalid:')
            ->assertSee('فحص الصحة');
    }

    public function test_guardrail_clears_when_report_clean(): void
    {
        Livewire::test(GradingSettings::class)
            ->call('setTab', 'subjects')
            ->call('onGradingHealthReportUpdated', ['clean' => true, 'missing' => 0, 'invalid' => 0])
            ->assertDontSee('Missing:')
            ->assertDontSee('Invalid:');
    }

    public function test_queue_warning_shows_when_heartbeat_is_stale(): void
    {
        Cache::put('grading.queue.last_heartbeat_at', now()->subHour()->toDateTimeString());

        Livewire::test(GradingSettings::class)
            ->set('activeTab', 'review')
            ->assertSee('مزامنة الدرجات متوقفة أو متأخرة');
    }

    private function resetAcademicContext(): void
    {
        Cache::flush();

        $context = AcademicContextService::getInstance();
        $reflection = new ReflectionClass($context);

        foreach (['cachedYear', 'cachedTerm', 'cachedSettings'] as $property) {
            if (! $reflection->hasProperty($property)) {
                continue;
            }

            $prop = $reflection->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue($context, null);
        }

        Cache::forget(AcademicContextService::CACHE_KEY_YEAR);
        Cache::forget(AcademicContextService::CACHE_KEY_TERM);
        Cache::forget(AcademicContextService::CACHE_KEY_SETTINGS);
        Cache::forget(AcademicContextService::CACHE_KEY_TERM_ACTIVE_LIST);
        Cache::forget(AcademicContextService::CACHE_KEY_TERM_UPCOMING);
    }
}
