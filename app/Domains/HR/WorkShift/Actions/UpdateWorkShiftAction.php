<?php

namespace App\Domains\HR\WorkShift\Actions;

use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Domains\HR\WorkShift\Services\WorkShiftLookupService;

class UpdateWorkShiftAction
{
    public function execute(WorkShift $workShift, array $data): bool
    {
        $updated = $workShift->update($data);

        // مسح الكاش لضمان تحديث القوائم
        WorkShiftLookupService::clearWorkShiftsCache();

        return $updated;
    }
}
