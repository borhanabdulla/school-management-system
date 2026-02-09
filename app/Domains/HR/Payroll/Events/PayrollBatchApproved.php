<?php

namespace App\Domains\HR\Payroll\Events;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollBatchApproved implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollBatch $batch)
    {
    }
}
