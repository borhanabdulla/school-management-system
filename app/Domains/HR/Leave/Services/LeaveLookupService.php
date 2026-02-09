<?php

namespace App\Domains\HR\Leave\Services;

use App\Domains\HR\Leave\Models\LeaveType;
use Illuminate\Support\Facades\Cache;

class LeaveLookupService
{
    public const CACHE_KEY_LEAVE_TYPES = 'leave_types_active';

    /**
     * Get active leave types, cached forever.
     */
    public function getLeaveTypes()
    {
        return Cache::rememberForever(self::CACHE_KEY_LEAVE_TYPES, function () {
            return LeaveType::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear leave types cache.
     */
    public static function clearLeaveTypesCache(): void
    {
        Cache::forget(self::CACHE_KEY_LEAVE_TYPES);
    }
}
