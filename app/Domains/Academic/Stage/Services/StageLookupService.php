<?php

namespace App\Domains\Academic\Stage\Services;

use App\Domains\Academic\Stage\Models\EducationalStage;
use Illuminate\Support\Facades\Cache;

class StageLookupService
{
    /**
     * Get all educational stages, cached.
     */
    public function getStages()
    {
        return Cache::remember('stages_all', 60 * 60 * 24, function () {
            return EducationalStage::orderBy('rank')->get();
        });
    }
}
