<?php

namespace App\Domains\HR\WorkShift\Actions;

use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Domains\HR\WorkShift\Services\WorkShiftLookupService;
use Exception;

class DeleteWorkShiftAction
{
    public function execute(WorkShift $workShift): bool
    {
        if (!$workShift->canBeDeleted()) {
            throw new Exception('لا يمكن حذف الوردية لأنها مرتبطة بموظفين.');
        }

        $deleted = $workShift->delete();

        // مسح الكاش
        WorkShiftLookupService::clearWorkShiftsCache();

        return $deleted;
    }
}
