<?php

namespace App\Domains\Academic\AcademicYear\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\DB;

class ArchiveAcademicYearAction
{
    public function execute(AcademicYear $year): void
    {
        DB::transaction(function () use ($year) {
            $year = AcademicYear::where('id', $year->id)->lockForUpdate()->first();

            if (!$year->canBeArchived()) {
                throw new InvalidOperationException("لا يمكن أرشفة هذه السنة. يجب أن تكون مغلقة أولاً.");
            }

            $year->update([
                'status' => AcademicYearStatus::Archived,
            ]);

            Log::info('Academic year archived', ['year_id' => $year->id, 'user_id' => auth()->id()]);
        });
    }
}
