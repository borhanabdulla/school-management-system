<?php

namespace Tests\Unit\Domain\HR;

use App\Domains\HR\Attendance\Services\Calculators\AttendanceCalculator;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class AttendanceCalculatorTest extends TestCase
{
    private AttendanceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new AttendanceCalculator();
    }

    public function test_calculate_delay_returns_zero_when_on_time()
    {
        $shiftStart = '08:00:00';
        $gracePeriod = 15;
        $clockIn = '2023-01-01 08:00:00';

        $delay = $this->calculator->calculateDelay($shiftStart, $gracePeriod, $clockIn);

        $this->assertEquals(0, $delay);
    }

    public function test_calculate_delay_returns_zero_within_grace_period()
    {
        $shiftStart = '08:00:00';
        $gracePeriod = 15;
        $clockIn = '2023-01-01 08:15:00';

        $delay = $this->calculator->calculateDelay($shiftStart, $gracePeriod, $clockIn);

        $this->assertEquals(0, $delay);
    }

    public function test_calculate_delay_returns_minutes_after_grace_period()
    {
        $shiftStart = '08:00:00';
        $gracePeriod = 15;
        $clockIn = '2023-01-01 08:20:00'; // 5 minutes late after grace period

        $delay = $this->calculator->calculateDelay($shiftStart, $gracePeriod, $clockIn);

        $this->assertEquals(5, $delay);
    }

    public function test_calculate_early_leave_returns_zero_when_after_end_time()
    {
        $shiftEnd = '16:00:00';
        $clockOut = '2023-01-01 16:05:00';

        $earlyLeave = $this->calculator->calculateEarlyLeave($shiftEnd, $clockOut);

        $this->assertEquals(0, $earlyLeave);
    }

    public function test_calculate_early_leave_returns_minutes_before_end_time()
    {
        $shiftEnd = '16:00:00';
        $clockOut = '2023-01-01 15:50:00'; // 10 minutes early

        $earlyLeave = $this->calculator->calculateEarlyLeave($shiftEnd, $clockOut);

        $this->assertEquals(10, $earlyLeave);
    }
}
