<?php

namespace App\Domains\HR\Substitution\Observers;

use App\Domains\HR\Substitution\Models\Substitution;
use Illuminate\Support\Facades\Cache;

/**
 * SubstitutionObserver - مراقب البدائل
 */
class SubstitutionObserver
{
    public function created(Substitution $substitution): void
    {
        $this->clearAffectedClassesCache($substitution);
    }

    public function updated(Substitution $substitution): void
    {
        $this->clearAffectedClassesCache($substitution);
    }

    public function deleted(Substitution $substitution): void
    {
        $this->clearAffectedClassesCache($substitution);
    }

    protected function clearAffectedClassesCache(Substitution $substitution): void
    {
        $date = $substitution->date;
        if ($date) {
            Cache::forget('hr_dashboard_affected_classes_' . $date->format('Y-m-d'));
        }
    }
}
