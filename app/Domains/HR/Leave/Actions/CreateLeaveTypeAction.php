<?php

namespace App\Domains\HR\Leave\Actions;

use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Services\LeaveLookupService;

class CreateLeaveTypeAction
{
    public function execute(array $data): LeaveType
    {
        $type = LeaveType::create($data);

        LeaveLookupService::clearLeaveTypesCache();

        return $type;
    }
}
