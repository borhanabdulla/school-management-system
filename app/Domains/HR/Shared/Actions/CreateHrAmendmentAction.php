<?php

namespace App\Domains\HR\Shared\Actions;

use App\Domains\HR\Shared\Models\HrAmendment;
use Illuminate\Database\Eloquent\Model;

class CreateHrAmendmentAction
{
    public function execute(
        Model $amendable,
        string $kind,
        string $reason,
        array $payload,
        int $requestedBy,
        ?int $staffId = null
    ): HrAmendment {
        return HrAmendment::create([
            'staff_id' => $staffId,
            'amendable_type' => $amendable::class,
            'amendable_id' => $amendable->getKey(),
            'kind' => $kind,
            'reason' => $reason,
            'payload' => $payload,
            'status' => 'pending',
            'requested_by' => $requestedBy,
        ]);
    }
}
