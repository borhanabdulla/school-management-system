<?php

namespace App\Domains\HR\Leave\Actions;

use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Services\LeaveLookupService;

class UpdateLeaveTypeAction
{
    public function execute(LeaveType $type, array $data): LeaveType
    {
        $type->update($data);

        LeaveLookupService::clearLeaveTypesCache();

        return $type->fresh();
    }
}
