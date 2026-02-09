<?php

namespace Tests\Unit\Domains\Academic\Student;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Services\AdmissionNumberService;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdmissionNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uses_db_active_year_when_cache_is_stale(): void
    {
        $staleYear = AcademicYear::factory()->create([
            'status' => 'pending',
            'start_date' => '2023-09-01',
        ]);
        $activeYear = AcademicYear::factory()->create([
            'status' => 'active',
            'start_date' => '2024-09-01',
        ]);

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, $staleYear, 3600);

        $student = Student::factory()->create();
        $service = app(AdmissionNumberService::class);

        $admissionNumber = $service->generateFor($student);

        $this->assertTrue(str_starts_with($admissionNumber, '2024'));
        $this->assertFalse(str_starts_with($admissionNumber, '2023'));
    }
}
