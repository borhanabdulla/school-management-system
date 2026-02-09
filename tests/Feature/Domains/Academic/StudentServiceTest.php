<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Student\Exceptions\NoActiveAcademicYearException;
use App\Domains\Academic\Student\Services\StudentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Infrastructure\Context\AcademicContextService;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_promote_students_requires_active_year(): void
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $fromGrade = Grade::create([
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
            'level_order' => 1,
        ]);
        $toGrade = Grade::create([
            'name' => 'Grade 2',
            'educational_stage_id' => $stage->id,
            'level_order' => 2,
        ]);

        $nextYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'pending',
        ]);

        $service = app(StudentService::class);

        $this->expectException(NoActiveAcademicYearException::class);

        $service->promoteStudents($fromGrade->id, $toGrade->id, $nextYear->id);
    }

    public function test_promote_students_uses_db_active_year_when_cache_is_stale(): void
    {
        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $fromGrade = Grade::create([
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
            'level_order' => 1,
        ]);
        $toGrade = Grade::create([
            'name' => 'Grade 2',
            'educational_stage_id' => $stage->id,
            'level_order' => 2,
        ]);

        AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'status' => 'pending',
        ]);
        $activeYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active',
        ]);

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, null, 3600);

        $nextYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'pending',
        ]);

        $service = app(StudentService::class);

        $count = $service->promoteStudents($fromGrade->id, $toGrade->id, $nextYear->id);

        $this->assertSame(0, $count);
        $this->assertDatabaseHas('academic_years', [
            'id' => $activeYear->id,
            'status' => 'active',
        ]);
    }
}
