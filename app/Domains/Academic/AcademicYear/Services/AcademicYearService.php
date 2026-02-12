<?php

namespace App\Domains\Academic\AcademicYear\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Infrastructure\Support\Helpers\DateHelper;
use App\Domains\Academic\AcademicYear\Exceptions\DateOverlapException;
use App\Domains\Academic\AcademicYear\Exceptions\InvalidDateRangeException;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Carbon\Carbon;

class AcademicYearService
{
    /**
     * التحقق من تداخل التواريخ
     * (منطق مركزي يُستخدم من Actions)
     */
    public function validateDateOverlap(
        Carbon $start,
        Carbon $end,
        ?int $ignoreId = null
    ): void {
        // 1. التحقق من صحة النطاق
        if (!DateHelper::isValidRange($start, $end)) {
            throw new InvalidDateRangeException();
        }

        // 2. فحص التداخل (SQL Optimized)
        $exists = AcademicYear::query()
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                    });
            })
            ->exists();

        if ($exists) {
            // لجلب الاسم للسنة المتداخلة (اختياري، قد يكلف استعلاماً إضافياً)
            $overlappingYear = AcademicYear::query()
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start_date', '<', $start)
                                ->where('end_date', '>', $end);
                        });
                })
                ->first();

            throw new DateOverlapException($overlappingYear ? $overlappingYear->name : 'Unknown');
        }
    }

    public function ensureSingleIncomingYear(Carbon $start, ?int $ignoreId = null): void
    {
        if (!$start->isFuture()) {
            return;
        }

        $exists = AcademicYear::query()
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->whereIn('status', [AcademicYearStatus::Pending, AcademicYearStatus::Active])
            ->where('start_date', '>', now())
            ->exists();

        if ($exists) {
            throw InvalidOperationException::make('لا يمكن إنشاء سنة قادمة جديدة. توجد سنة قادمة بالفعل.');
        }
    }
}
