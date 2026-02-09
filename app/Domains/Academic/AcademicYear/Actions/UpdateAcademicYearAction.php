<?php

namespace App\Domains\Academic\AcademicYear\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Data\AcademicYearData;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Illuminate\Support\Facades\DB;
use Exception;

class UpdateAcademicYearAction
{
    public function __construct(
        protected \App\Domains\Academic\AcademicYear\Validation\AcademicYearValidator $validator,
        protected \App\Domains\Academic\AcademicYear\Validation\AcademicYearTermValidator $termValidator,
        protected \App\Domains\Academic\Term\Services\TermService $termService,
        protected \App\Domains\Academic\AcademicYear\Services\AcademicYearService $yearService
    ) {
    }

    public function execute(AcademicYear $year, AcademicYearData $data): AcademicYear
    {
        $this->validator->validateNameFormat($data->name);

        // 1. Block Archived (Fast check before transaction)
        if ($year->isArchived()) {
            throw new \App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException('لا يمكن تعديل سنة مؤرشفة.');
        }

        return DB::transaction(function () use ($year, $data) {
            // Lock the record
            $year = AcademicYear::where('id', $year->id)->lockForUpdate()->first();

            // 2. Active or Closed: Update Name Only
            if (!$year->canEditDates()) {
                // Prevent date/structure changes
                if (
                    !$year->start_date->eq($data->start_date) ||
                    !$year->end_date->eq($data->end_date)
                ) {
                    throw new \App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException('لا يمكن تعديل تواريخ سنة نشطة أو مغلقة.');
                }

                $year->update(['name' => $data->name]);
                return $year;
            }

            // 3. Pending: Full Update
            if ($year->canEditDates()) {
                // التحقق من التواريخ إذا تغيرت
                if (
                    !$year->start_date->eq($data->start_date) ||
                    !$year->end_date->eq($data->end_date)
                ) {
                    // ✅ استخدام AcademicYearService (SQL محسّن)
                    // بدلاً من Validator (يجلب كل السجلات)
                    $this->yearService->validateDateOverlap(
                        $data->start_date,
                        $data->end_date,
                        $year->id
                    );

                    $this->yearService->ensureSingleIncomingYear(
                        $data->start_date,
                        $year->id
                    );
                }

                $year->update([
                    'name' => $data->name,
                    'start_date' => $data->start_date,
                    'end_date' => $data->end_date,
                ]);

                // 4. Smart Term Update
                if (!empty($data->terms)) {
                    $this->termValidator->validate($data->terms, $data->start_date, $data->end_date);
                    $this->syncTerms($year, $data->terms);
                }
            }

            return $year->fresh(['terms']);
        });
    }

    protected function syncTerms(AcademicYear $year, array $termsData): void
    {
        $existingIds = $year->terms()->pluck('id')->toArray();
        $inputIds = [];

        foreach ($termsData as $termData) {
            if (isset($termData['id']) && $termData['id']) {
                $inputIds[] = $termData['id'];
                // Update existing
                \App\Domains\Academic\Term\Models\Term::where('id', $termData['id'])
                    ->update([
                        'name' => $termData['name'],
                        'start_date' => $termData['start_date'],
                        'end_date' => $termData['end_date'],
                        'order_index' => $termData['order_index'] ?? 1,
                    ]);
            } else {
                // Create new
                $newTerm = $this->termService->createTerm([
                    'academic_year_id' => $year->id,
                    'name' => $termData['name'],
                    'start_date' => $termData['start_date'],
                    'end_date' => $termData['end_date'],
                    'order_index' => $termData['order_index'] ?? 1,
                    'status' => \App\Domains\Academic\Term\Enums\TermStatus::Pending->value,
                ]);
                $inputIds[] = $newTerm->id;
            }
        }

        // Delete removed terms
        $toDelete = array_diff($existingIds, $inputIds);
        if (!empty($toDelete)) {
            $termsToDelete = \App\Domains\Academic\Term\Models\Term::whereIn('id', $toDelete)->get();
            foreach ($termsToDelete as $term) {
                $this->termService->deleteTerm($term);
            }
        }
    }
}
