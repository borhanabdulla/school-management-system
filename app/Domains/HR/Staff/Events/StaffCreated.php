<?php

declare(strict_types=1);

namespace App\Domains\HR\Staff\Events;

use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StaffCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Staff $staff
    ) {
    }
}
