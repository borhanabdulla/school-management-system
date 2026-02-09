<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Observers;

use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class TemplateCategoryObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private GradingLookupService $lookupService
    ) {
    }

    public function created(TemplateCategory $category): void
    {
        $this->invalidateCache();
    }

    public function updated(TemplateCategory $category): void
    {
        $this->invalidateCache();
    }

    public function deleted(TemplateCategory $category): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        $this->lookupService->invalidateTemplatesCache();
    }
}
