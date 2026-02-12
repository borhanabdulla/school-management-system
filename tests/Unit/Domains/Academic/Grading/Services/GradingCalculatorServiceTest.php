<?php

namespace Tests\Unit\Domains\Academic\Grading\Services;

use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use Tests\TestCase;

class GradingCalculatorServiceTest extends TestCase
{
    private GradingCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(GradingCalculatorService::class);
    }

    public function test_normalize_calculates_correctly()
    {
        // 10/10 * 5 = 5
        $this->assertEquals(5.0, $this->calculator->normalize(10, 10, 5));

        // 5/10 * 5 = 2.5
        $this->assertEquals(2.5, $this->calculator->normalize(5, 10, 5));

        // 0/10 * 5 = 0
        $this->assertEquals(0.0, $this->calculator->normalize(0, 10, 5));

        // Rounding: 1/3 * 10 = 3.333... -> 3.33
        $this->assertEquals(3.33, $this->calculator->normalize(1, 3, 10));
    }

    public function test_normalize_handles_zero_max_score()
    {
        $this->assertEquals(0.0, $this->calculator->normalize(10, 0, 5));
    }

    public function test_calculate_attendance_score_no_deduction()
    {
        $settings = new GradebookSettings();
        $settings->attendance_max_score = 10;
        $settings->attendance_deduct_after = 2;
        $settings->attendance_deduct_per_absence = 1;

        // 0 absences
        $this->assertEquals(10.0, $this->calculator->calculateAttendanceScore(0, $settings));

        // 2 absences (threshold)
        $this->assertEquals(10.0, $this->calculator->calculateAttendanceScore(2, $settings));
    }

    public function test_calculate_attendance_score_with_deduction()
    {
        $settings = new GradebookSettings();
        $settings->attendance_max_score = 10;
        $settings->attendance_deduct_after = 2;
        $settings->attendance_deduct_per_absence = 1;

        // 3 absences (1 deductible) -> 10 - 1 = 9
        $this->assertEquals(9.0, $this->calculator->calculateAttendanceScore(3, $settings));

        // 5 absences (3 deductible) -> 10 - 3 = 7
        $this->assertEquals(7.0, $this->calculator->calculateAttendanceScore(5, $settings));
    }

    public function test_calculate_attendance_score_min_zero()
    {
        $settings = new GradebookSettings();
        $settings->attendance_max_score = 5;
        $settings->attendance_deduct_after = 0;
        $settings->attendance_deduct_per_absence = 2;

        // 3 absences -> 5 - (3*2) = -1 -> should be 0
        $this->assertEquals(0.0, $this->calculator->calculateAttendanceScore(3, $settings));
    }
}
