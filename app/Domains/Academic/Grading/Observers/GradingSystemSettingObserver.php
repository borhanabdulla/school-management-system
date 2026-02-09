<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Observers;

use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class GradingSystemSettingObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private GradingLookupService $lookupService
    ) {
    }

    public function saved(SystemSetting $setting): void
    {
        if ($setting->key === 'grading.scale') {
            $this->lookupService->invalidateScaleCache();
        }
    }
}
