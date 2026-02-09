<?php

namespace App\Domains\Academic\AcademicYear\Validation;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Infrastructure\Support\Helpers\DateHelper;
use App\Domains\Academic\AcademicYear\Exceptions\DateOverlapException;
use App\Domains\Academic\AcademicYear\Exceptions\InvalidDateRangeException;
use Carbon\Carbon;

class AcademicYearValidator
{
    /**
     * التحقق من صحة اسم السنة (أرقام فقط)
     */
    public function validateNameFormat(string $name): void
    {
        if (!preg_match('/^(\\d{4})-(\\d{4})$/', $name, $matches)) {
            throw new \App\Domains\Academic\AcademicYear\Exceptions\InvalidAcademicYearNameException(
                'صيغة اسم السنة يجب أن تكون مثل 2025-2026.'
            );
        }

        $start = (int) $matches[1];
        $end = (int) $matches[2];

        if ($end !== $start + 1) {
            throw new \App\Domains\Academic\AcademicYear\Exceptions\InvalidAcademicYearNameException(
                'السنة الثانية يجب أن تساوي السنة الأولى + 1.'
            );
        }
    }

    /**
     * التحقق من تداخل التواريخ
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

        // 2. فحص التداخل
        $overlapping = AcademicYear::where('id', '!=', $ignoreId)
            ->get()
            ->first(fn($year) => DateHelper::datesOverlap(
                $start,
                $end,
                $year->start_date,
                $year->end_date
            ));

        if ($overlapping) {
            throw new DateOverlapException($overlapping->name);
        }
    }
}
