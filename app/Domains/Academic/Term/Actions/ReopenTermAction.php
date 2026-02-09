<?php

namespace App\Domains\Academic\Term\Actions;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Infrastructure\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class ReopenTermAction
{
    public function execute(Term $term): void
    {
        DB::transaction(function () use ($term) {
            $activeYear = AcademicYear::query()
                ->where('status', AcademicYearStatus::Active)
                ->lockForUpdate()
                ->first();

            $term = Term::where('id', $term->id)->lockForUpdate()->firstOrFail();

            if ($term->status === TermStatus::Active) {
                throw BusinessRuleException::make('الفصل الدراسي نشط بالفعل.');
            }
            if ($term->status !== TermStatus::Completed) {
                throw BusinessRuleException::make('إعادة فتح الفصل مسموحة فقط للفصول المكتملة.');
            }

            if (!$activeYear || $term->academic_year_id !== $activeYear->id) {
                throw BusinessRuleException::make('لا يمكن إعادة فتح فصل لسنة غير نشطة.');
            }

            $activeTerm = Term::query()
                ->where('academic_year_id', $activeYear->id)
                ->where('status', TermStatus::Active)
                ->lockForUpdate()
                ->first();

            if ($activeTerm && $activeTerm->id !== $term->id) {
                throw BusinessRuleException::make('لا يمكن إعادة فتح فصل مع وجود فصل نشط.');
            }

            $term->update(['status' => TermStatus::Pending]);

            DB::afterCommit(function () {
                school()->invalidateTerm();
            });
        });
    }
}
