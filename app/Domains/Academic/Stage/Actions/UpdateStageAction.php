<?php

namespace App\Domains\Academic\Stage\Actions;

use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Stage\Data\StageData;
use Illuminate\Support\Facades\DB;

class UpdateStageAction
{
    public function execute(EducationalStage $stage, StageData $data): EducationalStage
    {
        return DB::transaction(function () use ($stage, $data) {
            $stage->update($data->toArray());
            \App\Domains\Academic\Events\StructureChanged::dispatch(
                \App\Domains\Academic\Events\StructureChanged::TYPE_STAGE,
                \App\Domains\Academic\Events\StructureChanged::ACTION_UPDATED,
                $stage
            );

            return $stage;
        });
    }
}
