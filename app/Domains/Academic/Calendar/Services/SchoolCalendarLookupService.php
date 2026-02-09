<?php

namespace App\Domains\Academic\Calendar\Services;

use App\Domains\Academic\Calendar\Models\SchoolEvent;
use Illuminate\Support\Facades\Cache;

class SchoolCalendarLookupService
{
    public const CACHE_KEY_SCHOOL_EVENTS = 'school_calendar_events';

    /**
     * Get school events for the current year, cached.
     */
    public function getSchoolEventsForCurrentYear()
    {
        return Cache::remember(self::CACHE_KEY_SCHOOL_EVENTS, 86400, function () {
            return SchoolEvent::currentYear()
                ->orderBy('start_date')
                ->get();
        });
    }

    /**
     * Clear school events cache.
     */
    public static function clearSchoolEventsCache(): void
    {
        Cache::forget(self::CACHE_KEY_SCHOOL_EVENTS);
    }
}
