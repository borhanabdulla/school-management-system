<?php

namespace App\Domains\Shared\Services;

class StatsHelper
{
    /**
     * Calculate occupancy percentage.
     *
     * @param int $current Current count (e.g., students enrolled)
     * @param int $max Maximum capacity
     * @return float Percentage (0-100)
     */
    public static function calculateOccupancy(int $current, int $max): float
    {
        if ($max <= 0) {
            return 0;
        }

        return round(($current / $max) * 100, 1);
    }

    /**
     * Determine occupancy status color/level.
     *
     * @param float $percentage
     * @return string 'success', 'warning', 'danger'
     */
    public static function getOccupancyLevel(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'danger';
        }
        if ($percentage >= 80) {
            return 'warning';
        }
        return 'success';
    }

    /**
     * Calculate percentage with safe zero handling.
     *
     * @param float $numerator
     * @param float $denominator
     * @param int $precision
     * @return float
     */
    public static function percentage(float $numerator, float $denominator, int $precision = 0): float
    {
        if ($denominator <= 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, $precision);
    }
}
