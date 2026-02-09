<?php

namespace App\Domains\HR\Leave\Events;

use App\Domains\HR\Leave\Models\LeaveRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * LeaveRequestStatusChanged - حدث تغيير حالة طلب الإجازة
 */
class LeaveRequestStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {
    }
}
