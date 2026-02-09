<?php

namespace App\Domains\Academic\AcademicYear\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Data\AcademicYearData;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\Academic\Term\Services\TermService;

class CreateAcademicYearAction
{
    public function __construct(
        protected \App\Domains\Academic\AcademicYear\Services\AcademicYearService $service,
        protected \App\Domains\Academic\AcademicYear\Validation\AcademicYearValidator $validator,
        protected \App\Domains\Academic\AcademicYear\Validation\AcademicYearTermValidator $termValidator,
        protected \App\Domains\Academic\AcademicYear\Actions\ActivateAcademicYearAction $activateAction,
        protected TermService $termService
    ) {
    }

    public function execute(AcademicYearData $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {
            $this->validator->validateNameFormat($data->name);

            if ($data->status && !in_array($data->status, [AcademicYearStatus::Pending, AcademicYearStatus::Active], true)) {
                throw new \App\Domains\Academic\AcademicYear\Exceptions\ActiveYearCreationNotAllowedException();
            }

            $this->service->ensureSingleIncomingYear($data->start_date);

            // 1. التحقق من التواريخ (داخل الترانزاكشن لتقليل احتمالية التداخل)
            // ملاحظة: لمنع التداخل تماماً نحتاج إلى Table Lock أو Serializable Isolation
            $this->service->validateDateOverlap($data->start_date, $data->end_date);

            $this->termValidator->validate($data->terms, $data->start_date, $data->end_date);

            $shouldActivate = $data->status === AcademicYearStatus::Active
                && !AcademicYear::active()->lockForUpdate()->exists();

            $status = AcademicYearStatus::Pending;

            // 3. إنشاء السنة
            $year = AcademicYear::create([
                'name' => $data->name,
                'start_date' => $data->start_date,
                'end_date' => $data->end_date,
                'status' => $status,
            ]);

            // 5. إنشاء الفصول
            foreach ($data->terms as $termData) {
                $this->termService->createTerm([
                    'academic_year_id' => $year->id,
                    'name' => $termData['name'],
                    'start_date' => $termData['start_date'],
                    'end_date' => $termData['end_date'],
                    'order_index' => $termData['order_index'] ?? 1,
                    'status' => \App\Domains\Academic\Term\Enums\TermStatus::Pending,
                ]);
            }

            if ($shouldActivate) {
                $this->activateAction->execute($year->fresh());
            }

            Log::info('Academic year created', [
                'year_id' => $year->id,
                'name' => $year->name
            ]);

            return $year->fresh(['terms']);
        });
    }
}
