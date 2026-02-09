<?php

namespace App\Domains\HR\Payroll\Exceptions;

use RuntimeException;

class PeriodLockedException extends RuntimeException
{
    public function __construct(
        public readonly string $periodStart,
        public readonly string $periodEnd,
        public readonly string $status,
        public readonly ?int $batchId = null
    ) {
        $message = "الفترة {$periodStart} - {$periodEnd} مقفلة ({$status}).";
        if ($batchId) {
            $message .= " رقم المسير: {$batchId}.";
        }

        parent::__construct($message);
    }
}
