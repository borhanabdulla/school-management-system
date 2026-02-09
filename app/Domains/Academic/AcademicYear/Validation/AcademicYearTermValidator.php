<?php

declare(strict_types=1);

namespace App\Domains\Academic\AcademicYear\Validation;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AcademicYearTermValidator
{
    /**
     * Validate term dates within the given academic year range.
     *
     * @param array<int, array<string, mixed>> $terms
     */
    public function validate(array $terms, Carbon $yearStart, Carbon $yearEnd): void
    {
        $normalized = [];

        foreach ($terms as $index => $term) {
            if (empty($term['start_date']) || empty($term['end_date'])) {
                throw ValidationException::withMessages([
                    'terms' => 'تواريخ الفصول مطلوبة بالكامل.',
                ]);
            }

            $start = Carbon::parse($term['start_date']);
            $end = Carbon::parse($term['end_date']);

            if ($start->lt($yearStart) || $end->gt($yearEnd)) {
                throw ValidationException::withMessages([
                    'terms' => 'تواريخ الفصول يجب أن تكون داخل نطاق السنة الدراسية.',
                ]);
            }

            if ($start->gte($end)) {
                throw ValidationException::withMessages([
                    'terms' => 'تاريخ نهاية الفصل يجب أن يكون بعد بدايته.',
                ]);
            }

            $normalized[] = [
                'start' => $start,
                'end' => $end,
                'index' => $index,
            ];
        }

        usort($normalized, fn($a, $b) => $a['start'] <=> $b['start']);

        $previous = null;
        foreach ($normalized as $item) {
            if ($previous && $item['start']->lt($previous['end'])) {
                throw ValidationException::withMessages([
                    'terms' => 'يوجد تداخل زمني بين الفصول الدراسية.',
                ]);
            }
            $previous = $item;
        }
    }
}
