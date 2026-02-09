<?php

namespace App\Domains\Academic\Grade\Actions;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grade\Data\GradeData;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use Illuminate\Support\Facades\DB;

class UpdateGradeAction
{
    public function execute(Grade $grade, GradeData $data): Grade
    {
        return DB::transaction(function () use ($grade, $data) {
            $grade->update($data->toArray());
            \App\Domains\Academic\Events\StructureChanged::dispatch(
                \App\Domains\Academic\Events\StructureChanged::TYPE_GRADE,
                \App\Domains\Academic\Events\StructureChanged::ACTION_UPDATED,
                $grade
            );

            return $grade;
        });
    }
}
