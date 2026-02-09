<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Actions\AssignTeacherToCourseAction;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AssignTeacherToCourseActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uses_db_active_year_and_term_when_cache_is_stale()
    {
        Cache::flush();

        $cachedYear = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Pending,
        ]);
        $cachedTerm = Term::factory()->create([
            'academic_year_id' => $cachedYear->id,
            'status' => TermStatus::Pending,
        ]);

        $activeYear = AcademicYear::factory()->active()->create();
        $activeTerm = Term::factory()->active()->create([
            'academic_year_id' => $activeYear->id,
        ]);

        $section = ClassSection::factory()->create([
            'academic_year_id' => $activeYear->id,
        ]);
        $subject = Subject::factory()->create();
        $teacher = Teacher::factory()->create();

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, $cachedYear, 60 * 60 * 24);
        Cache::put(AcademicContextService::CACHE_KEY_TERM, $cachedTerm, 60 * 60 * 24);

        $offering = app(AssignTeacherToCourseAction::class)->execute($section, $subject, $teacher);

        $this->assertEquals($activeYear->id, $offering->academic_year_id);
        $this->assertEquals($activeTerm->id, $offering->term_id);
        $this->assertNotEquals($cachedYear->id, $offering->academic_year_id);
    }
}
