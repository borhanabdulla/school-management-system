<?php

namespace App\Domains\Academic\Grade\Actions;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use Illuminate\Support\Facades\DB;

class DeleteGradeAction
{
    public function execute(Grade $grade): void
    {
        DB::transaction(function () use ($grade) {
            $grade->delete();

            \App\Domains\Academic\Events\StructureChanged::dispatch(
                \App\Domains\Academic\Events\StructureChanged::TYPE_GRADE,
                \App\Domains\Academic\Events\StructureChanged::ACTION_DELETED,
                $grade
            );
        });
    }
}
