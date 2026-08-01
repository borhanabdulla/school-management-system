<?php

namespace App\Domains\HR\Leave\Actions;

use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Services\LeaveLookupService;

class DeleteLeaveTypeAction
{
    public function execute(LeaveType $type): void
    {
        $type->delete();

        LeaveLookupService::clearLeaveTypesCache();
    }
}
