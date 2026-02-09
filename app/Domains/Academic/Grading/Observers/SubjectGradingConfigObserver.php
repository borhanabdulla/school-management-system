<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Observers;

use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class SubjectGradingConfigObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private GradingLookupService $lookupService
    ) {
    }

    public function created(SubjectGradingConfig $config): void
    {
        $this->invalidateCache();
    }

    public function updated(SubjectGradingConfig $config): void
    {
        $this->invalidateCache();
    }

    public function deleted(SubjectGradingConfig $config): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        $this->lookupService->invalidateTemplatesCache();
    }
}
