<?php

namespace App\Domains\Academic\Grade\Actions;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grade\Data\GradeData;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use Illuminate\Support\Facades\DB;

class CreateGradeAction
{
    public function execute(GradeData $data): Grade
    {
        return DB::transaction(function () use ($data) {
            $grade = Grade::create($data->toArray());

            \App\Domains\Academic\Events\StructureChanged::dispatch(
                \App\Domains\Academic\Events\StructureChanged::TYPE_GRADE,
                \App\Domains\Academic\Events\StructureChanged::ACTION_CREATED,
                $grade
            );

            // Clear cache
            GradeLookupService::invalidateCache($grade->educational_stage_id);

            return $grade;
        });
    }
}
