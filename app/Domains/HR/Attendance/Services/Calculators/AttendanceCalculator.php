<?php

namespace App\Domains\HR\Attendance\Services\Calculators;

use Carbon\Carbon;

class AttendanceCalculator
{
    /**
     * Calculate delay minutes based on shift start time and actual clock in.
     */
    public function calculateDelay(string $shiftStartTime, int $gracePeriodMinutes, string $clockIn): int
    {
        $clockIn = Carbon::parse($clockIn);

        // Create threshold time (Start Time + Grace Period)
        // We use the date from clockIn to ensure we are comparing times on the same day
        $threshold = Carbon::parse($shiftStartTime)
            ->setDate($clockIn->year, $clockIn->month, $clockIn->day)
            ->addMinutes($gracePeriodMinutes);

        // If arrived before or at threshold, no delay
        if ($clockIn->lte($threshold)) {
            return 0;
        }

        // Calculate delay (difference in minutes)
        return (int) $threshold->diffInMinutes($clockIn);
    }

    /**
     * Calculate early leave minutes based on shift end time and actual clock out.
     */
    public function calculateEarlyLeave(string $shiftEndTime, string $clockOut): int
    {
        $clockOut = Carbon::parse($clockOut);

        $endTime = Carbon::parse($shiftEndTime)
            ->setDate($clockOut->year, $clockOut->month, $clockOut->day);

        // If left after or at end time, no early leave
        if ($clockOut->gte($endTime)) {
            return 0;
        }

        return (int) abs($endTime->diffInMinutes($clockOut, false));
    }

    /**
     * Determine if the attendance is considered "Late".
     */
    public function isLate(int $delayMinutes): bool
    {
        return $delayMinutes > 0;
    }
}
