<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Observers;

use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class GradingTemplateObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private GradingLookupService $lookupService
    ) {
    }

    public function created(GradingTemplate $template): void
    {
        $this->invalidateCache();
    }

    public function updated(GradingTemplate $template): void
    {
        $this->invalidateCache();
    }

    public function deleted(GradingTemplate $template): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        $this->lookupService->invalidateTemplatesCache();
    }
}
