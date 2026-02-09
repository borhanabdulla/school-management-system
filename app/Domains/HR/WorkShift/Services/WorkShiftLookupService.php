<?php

namespace App\Domains\HR\WorkShift\Services;

use App\Domains\HR\WorkShift\Models\WorkShift;
use Illuminate\Support\Facades\Cache;

class WorkShiftLookupService
{
    public const CACHE_KEY_WORK_SHIFTS = 'work_shifts_active';

    /**
     * Get active work shifts, cached forever.
     */
    public function getActiveWorkShifts()
    {
        return Cache::rememberForever(self::CACHE_KEY_WORK_SHIFTS, function () {
            return WorkShift::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get work shifts for the current season.
     */
    public function getCurrentSeasonShifts()
    {
        $currentMonth = now()->month;
        $isSummer = $currentMonth >= 6 && $currentMonth <= 9;
        $season = $isSummer ? 'summer' : 'winter';

        return Cache::remember("work_shifts_season_{$season}", 3600 * 24, function () use ($season) {
            return WorkShift::where('is_active', true)
                ->where(function ($q) use ($season) {
                    $q->where('season', $season)
                        ->orWhere('season', 'all');
                })
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear work shifts cache.
     */
    public static function clearWorkShiftsCache(): void
    {
        Cache::forget(self::CACHE_KEY_WORK_SHIFTS);
        Cache::forget('work_shifts_season_summer');
        Cache::forget('work_shifts_season_winter');
    }
}
