<?php

namespace Tests\Unit;

use App\Domains\Academic\AcademicYear\Validation\AcademicYearTermValidator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcademicYearTermValidatorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rejects_terms_outside_year_range()
    {
        $validator = new AcademicYearTermValidator();

        $this->expectException(ValidationException::class);

        $validator->validate([
            [
                'name' => 'Term 1',
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-01',
                'order_index' => 1,
            ],
        ], Carbon::parse('2025-09-01'), Carbon::parse('2026-06-30'));
    }

    /** @test */
    public function it_rejects_overlapping_terms()
    {
        $validator = new AcademicYearTermValidator();

        $this->expectException(ValidationException::class);

        $validator->validate([
            [
                'name' => 'Term 1',
                'start_date' => '2025-09-01',
                'end_date' => '2025-12-31',
                'order_index' => 1,
            ],
            [
                'name' => 'Term 2',
                'start_date' => '2025-12-01',
                'end_date' => '2026-03-01',
                'order_index' => 2,
            ],
        ], Carbon::parse('2025-09-01'), Carbon::parse('2026-06-30'));
    }
}
