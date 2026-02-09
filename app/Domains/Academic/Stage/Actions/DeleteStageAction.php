<?php

namespace App\Domains\Academic\Stage\Actions;

use App\Domains\Academic\Stage\Models\EducationalStage;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Academic\StageHasGradesException;

class DeleteStageAction
{
    public function execute(EducationalStage $stage): void
    {
        // Business Logic Protection: منع حذف المرحلة إذا كانت تحتوي على صفوف
        if ($stage->grades()->exists()) {
            throw new StageHasGradesException();
        }

        DB::transaction(function () use ($stage) {
            $stage->delete();

            \App\Domains\Academic\Events\StructureChanged::dispatch(
                \App\Domains\Academic\Events\StructureChanged::TYPE_STAGE,
                \App\Domains\Academic\Events\StructureChanged::ACTION_DELETED,
                $stage
            );
        });
    }
}
