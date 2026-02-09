<?php

namespace App\Domains\Finance\Exceptions;

use App\Infrastructure\Exceptions\BusinessRuleException;

class ResultsBlockedByFinanceException extends BusinessRuleException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            "Academic results are blocked due to financial clearance status: {$reason}",
            'RESULTS_BLOCKED_BY_FINANCE',
            ['reason' => $reason]
        );
    }
}
