<?php

namespace App\Domains\Academic\Stage\Actions;

use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Stage\Data\StageData;
use App\Domains\Academic\Events\StructureChanged;
use Illuminate\Support\Facades\DB;

class CreateStageAction
{
    public function execute(StageData $data): EducationalStage
    {
        return DB::transaction(function () use ($data) {
            $stage = EducationalStage::create($data->toArray());

            \App\Domains\Academic\Events\StructureChanged::dispatch(
                \App\Domains\Academic\Events\StructureChanged::TYPE_STAGE,
                \App\Domains\Academic\Events\StructureChanged::ACTION_CREATED,
                $stage
            );

            return $stage;
        });
    }
}
