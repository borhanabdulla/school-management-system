<?php

namespace App\Domains\HR\WorkShift\Actions;

use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Domains\HR\WorkShift\Services\WorkShiftLookupService;

class ToggleWorkShiftStatusAction
{
    public function execute(WorkShift $workShift): bool
    {
        $updated = $workShift->update(['is_active' => !$workShift->is_active]);

        // مسح الكاش
        WorkShiftLookupService::clearWorkShiftsCache();

        return $updated;
    }
}
