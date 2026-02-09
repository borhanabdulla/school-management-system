<?php

declare(strict_types=1);

namespace App\Domains\Academic\Promotion\Decision;

use App\Domains\Academic\Results\Enums\ResultDecision;
use App\Domains\Academic\Results\Models\AnnualResult;

final class PromotionDecisionStrategy
{
    public function decide(AnnualResult $result, int $maxFailedForConditional): ResultDecision
    {
        if ($result->failed_count === 0) {
            return ResultDecision::Pass;
        }

        if ($result->failed_count <= $maxFailedForConditional) {
            return ResultDecision::Conditional;
        }

        return ResultDecision::Fail;
    }
}
