<?php

namespace App\Domains\Academic\AcademicYear\Validation;


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

}
