<?php

declare(strict_types=1);

namespace App\Domains\Academic\Results\Services;

use App\Domains\Academic\Results\Models\TermResult;

class TermResultFailureRecorder
{
    /**
     * @param array<int, array{category_id: int, threshold: float, actual_percentage: float}> $failures
     */
    public function syncFailures(TermResult $termResult, array $failures): void
    {
        $termResult->failures()->delete();

        if ($failures === []) {
            return;
        }

        $payload = [];
        foreach ($failures as $failure) {
            $payload[] = [
                'template_category_id' => $failure['category_id'],
                'reason' => 'threshold',
                'required_min' => $failure['threshold'],
                'actual_percentage' => $failure['actual_percentage'],
            ];
        }

        $termResult->failures()->createMany($payload);
    }
}
