<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Models\Country;
use Illuminate\Support\Facades\Cache;
use App\Domains\Shared\Enums\DayOfWeek;

class SharedLookupService
{
    /**
     * Get all countries, cached.
     */
    public function getCountries()
    {
        return Cache::remember('countries_all', 60 * 60 * 24 * 7, function () {
            return Country::orderBy('name')->get();
        });
    }

    /**
     * Get day names from Enum.
     */
    public function getDayNames()
    {
        return Cache::rememberForever('day_names_arabic', function () {
            return collect(DayOfWeek::cases())->mapWithKeys(fn($day) => [
                $day->value => $day->label()
            ])->toArray();
        });
    }
}
