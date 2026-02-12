<?php

namespace App\Domains\Academic\Term\Observers;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Services\TermLookupService;
use Illuminate\Support\Facades\Log;

class TermObserver
{
    /**
     * Handle the Term "created" event.
     */
    public function __construct(
        private \App\Domains\Academic\Grading\Services\GradebookMonthService $monthService
    ) {
    }

    /**
     * Handle the Term "created" event.
     */
    public function created(Term $term): void
    {
        $this->monthService->generateForTerm($term);
        $this->clearCache();
        Log::info('Term created and months generated', ['term_id' => $term->id]);
    }

    /**
     * Handle the Term "updated" event.
     */
    public function updated(Term $term): void
    {
        // Only regenerate if dates changed
        if ($term->wasChanged(['start_date', 'end_date'])) {
            $this->monthService->generateForTerm($term);
            Log::info('Term dates updated, months regenerated', ['term_id' => $term->id]);
        }

        $this->clearCache();
        Log::info('Term updated', ['term_id' => $term->id]);
    }

    /**
     * Handle the Term "deleted" event.
     */
    public function deleted(Term $term): void
    {
        $this->clearCache();
        Log::info('Term deleted', ['term_id' => $term->id]);
    }

    protected function clearCache(): void
    {
        app(TermLookupService::class)->invalidateCache();
    }
}
