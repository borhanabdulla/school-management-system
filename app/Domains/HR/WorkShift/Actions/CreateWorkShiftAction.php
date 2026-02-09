<?php

namespace App\Domains\HR\WorkShift\Actions;

use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Domains\HR\WorkShift\Services\WorkShiftLookupService;

class CreateWorkShiftAction
{
    public function execute(array $data): WorkShift
    {
        $workShift = WorkShift::create($data);

        // مسح الكاش لضمان تحديث القوائم
        WorkShiftLookupService::clearWorkShiftsCache();

        return $workShift;
    }
}
