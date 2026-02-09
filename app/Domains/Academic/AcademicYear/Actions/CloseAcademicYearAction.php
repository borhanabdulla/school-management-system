<?php

namespace App\Domains\Academic\AcademicYear\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseAcademicYearAction
{
    public function __construct(
        protected \App\Domains\Academic\AcademicYear\Validation\AcademicYearClosureValidator $closureValidator
    ) {
    }

    public function execute(AcademicYear $year): void
    {
        if ($year->status !== AcademicYearStatus::Active) {
            throw new \App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException('لا يمكن إغلاق سنة غير نشطة.');
        }

        $check = $this->closureValidator->validate($year);
        if (!$check['can']) {
            throw InvalidOperationException::cannotClose(
                'السنة الدراسية',
                implode('، ', $check['issues'])
            );
        }

        DB::transaction(function () use ($year) {
            $year = AcademicYear::where('id', $year->id)->lockForUpdate()->first();

            $year->update(['status' => AcademicYearStatus::Closed]);

            Log::info('Academic year closed', ['year_id' => $year->id]);
        });
    }
}
