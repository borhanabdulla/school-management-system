<?php

declare(strict_types=1);

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Models\Guardian;

class DeleteGuardianAction
{
    public function execute(Guardian $guardian): void
    {
        $guardian->delete();
    }
}
