<?php

namespace App\Domains\Academic\Term\Actions;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Exceptions\BusinessRuleException;

class ActivateTermAction
{
    public function execute(Term $term): void
    {
        DB::transaction(function () use ($term) {
            // 1. Ensure the academic year is active (DB-authoritative)
            $activeYear = AcademicYear::query()
                ->where('status', AcademicYearStatus::Active)
                ->lockForUpdate()
                ->first();

            // 2. Lock the target term
            $term = Term::where('id', $term->id)->lockForUpdate()->firstOrFail();

            if (!$activeYear || $term->academic_year_id !== $activeYear->id) {
                throw new \App\Domains\Academic\Term\Exceptions\TermYearNotActiveException($term->id, $term->academic_year_id);
            }

            if ($term->status === TermStatus::Active) {
                return;
            }
            if ($term->status !== TermStatus::Pending) {
                throw BusinessRuleException::make('لا يمكن تفعيل فصل مكتمل. لإعادة فتحه يلزم إجراء إداري منفصل.');
            }

            // 3. Handle Term Transition (complete current active term)
            $activeTerms = Term::query()
                ->where('academic_year_id', $activeYear->id)
                ->where('status', TermStatus::Active)
                ->lockForUpdate()
                ->get();

            foreach ($activeTerms as $activeTerm) {
                if ($activeTerm->id !== $term->id) {
                    $activeTerm->update(['status' => TermStatus::Completed]);
                }
            }

            // 4. Activate the target term
            $term->update(['status' => TermStatus::Active]);

            DB::afterCommit(function () {
                school()->invalidateTerm();
            });
        });
    }
}
