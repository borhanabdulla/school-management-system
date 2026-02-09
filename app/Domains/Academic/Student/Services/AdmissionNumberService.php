<?php

namespace App\Domains\Academic\Student\Services;

use App\Domains\Academic\Student\Models\Student;

class AdmissionNumberService
{
    /**
     * Build an admission number once a student exists.
     */
    public function generateFor(Student $student): string
    {
        $year = school()->activeYear();
        if (!$year) {
            throw new \App\Domains\Academic\Student\Exceptions\NoActiveAcademicYearException();
        }

        $prefix = $year->start_date?->format('Y') ?? now()->format('Y');
        $sequence = str_pad($student->id, 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$sequence}";
    }
}
