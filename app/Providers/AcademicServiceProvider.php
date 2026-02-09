<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Observers\TermObserver;

class AcademicServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Observers
        Term::observe(TermObserver::class);
    }
}
