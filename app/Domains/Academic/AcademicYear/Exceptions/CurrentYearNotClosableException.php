<?php

namespace App\Domains\Academic\AcademicYear\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

class CurrentYearNotClosableException extends InvalidOperationException
{
    public function __construct(string $yearName, array $issues)
    {
        $message = "لا يمكن تفعيل السنة الجديدة لأن السنة الحالية ({$yearName}) غير جاهزة للإغلاق:\n" . implode("\n", $issues);
        parent::__construct($message, 'close_current_year', 'validation_issues');
    }
}
