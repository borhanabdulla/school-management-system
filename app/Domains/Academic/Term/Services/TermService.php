<?php

namespace App\Domains\Academic\Term\Services;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Infrastructure\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TermService
{
    public function __construct(
        protected \App\Domains\Academic\Term\Actions\CreateTermAction $createAction,
        protected \App\Domains\Academic\Term\Actions\UpdateTermAction $updateAction,
        protected \App\Domains\Academic\Term\Actions\DeleteTermAction $deleteAction,
    ) {
    }

    /**
     * Create a new term with strict temporal validation.
     */
    public function createTerm(array $data): Term
    {
        if (($data['status'] ?? null) === TermStatus::Active->value) {
            throw BusinessRuleException::make('تفعيل الفصل يجب أن يتم عبر ActivateTermAction.');
        }

        return DB::transaction(function () use ($data) {
            // 1. التحقق الصارم من التواريخ
            $this->validateTermDates($data);

            // 2. تنفيذ الإنشاء عبر الأكشن
            $termData = \App\Domains\Academic\Term\Data\TermData::fromArray($data);
            $term = $this->createAction->execute($termData);

            // 3. إبطال الكاش
            school()->invalidateTerm();

            return $term;
        });
    }

    /**
     * Update term with logic checks.
     */
    public function updateTerm(Term $term, array $data): Term
    {
        if (($data['status'] ?? null) === TermStatus::Active->value) {
            throw BusinessRuleException::make('تفعيل الفصل يجب أن يتم عبر ActivateTermAction.');
        }

        return DB::transaction(function () use ($term, $data) {
            // دمج البيانات الجديدة مع القديمة
            $fullData = array_merge($term->toArray(), $data);
            $fullData['academic_year_id'] = $term->academic_year_id;

            // التحقق من التواريخ
            if (isset($data['start_date']) || isset($data['end_date'])) {
                $this->validateTermDates($fullData, $term->id);
            }

            // تنفيذ التحديث عبر الأكشن
            $termData = \App\Domains\Academic\Term\Data\TermData::fromArray($fullData);
            $updatedTerm = $this->updateAction->execute($term, $termData);

            // إبطال الكاش
            school()->invalidateTerm();

            return $updatedTerm;
        });
    }

    /**
     * Core Validation Logic (The Brain)
     */
    protected function validateTermDates(array $data, $ignoreTermId = null): void
    {
        $year = AcademicYear::findOrFail($data['academic_year_id']);

        $termStart = Carbon::parse($data['start_date']);
        $termEnd = Carbon::parse($data['end_date']);
        $yearStart = Carbon::parse($year->start_date);
        $yearEnd = Carbon::parse($year->end_date);

        if ($termStart->lt($yearStart) || $termEnd->gt($yearEnd)) {
            throw ValidationException::withMessages([
                'start_date' => "التواريخ خارج نطاق السنة الدراسية ({$yearStart->format('Y-m-d')} إلى {$yearEnd->format('Y-m-d')}).",
            ]);
        }

        if ($termStart->gte($termEnd)) {
            throw ValidationException::withMessages([
                'end_date' => "تاريخ النهاية يجب أن يكون بعد تاريخ البداية.",
            ]);
        }

        $overlapping = Term::where('academic_year_id', $year->id)
            ->where('id', '!=', $ignoreTermId)
            ->where(function ($query) use ($termStart, $termEnd) {
                $query->where('start_date', '<', $termEnd)
                    ->where('end_date', '>', $termStart);
            })
            ->exists();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'start_date' => 'هذه الفترة تتداخل زمنياً مع فصل دراسي آخر موجود مسبقاً في نفس السنة.',
            ]);
        }
    }

    /**
     * Delete logic with Dependency Investigation
     */
    public function deleteTerm(Term $term): void
    {
        // 1. تحقق الحالة
        if ($term->status === \App\Domains\Academic\Term\Enums\TermStatus::Active) {
            throw ValidationException::withMessages(['error' => 'لا يمكن حذف الفصل الدراسي النشط حالياً.']);
        }

        // 2. التحقيق في التبعيات
        $dependencies = [];
        if (method_exists($term, 'timetables') && $term->timetables()->exists())
            $dependencies[] = "جداول حصص";
        if (method_exists($term, 'grades') && $term->grades()->exists())
            $dependencies[] = "درجات مرصودة";
        if (method_exists($term, 'attendances') && $term->attendances()->exists())
            $dependencies[] = "سجلات حضور";

        if (!empty($dependencies)) {
            $reason = implode(' و ', $dependencies);
            throw ValidationException::withMessages([
                'error' => "لا يمكن حذف هذا الفصل لأنه يحتوي على: ($reason). قم بأرشفته بدلاً من ذلك."
            ]);
        }

        // 3. الحذف عبر الأكشن
        $this->deleteAction->execute($term);

        // 4. إبطال الكاش
        school()->invalidateTerm();
    }

}
