<?php

namespace App\Domains\Academic\Calendar\Observers;

use App\Domains\Academic\Calendar\Models\SchoolEvent;
use App\Domains\Academic\Calendar\Services\SchoolCalendarService;
use Illuminate\Support\Facades\Log;

/**
 * SchoolEventObserver - مراقب أحداث المدرسة
 */
class SchoolEventObserver
{
    public function __construct(
        protected SchoolCalendarService $calendarService
    ) {
    }

    public function created(SchoolEvent $schoolEvent): void
    {
        $this->calendarService->clearCache();

        if ($schoolEvent->type === 'emergency') {
            Log::info("🚨 Emergency holiday created: {$schoolEvent->title}", [
                'event_id' => $schoolEvent->id,
                'start_date' => $schoolEvent->start_date->format('Y-m-d'),
                'end_date' => $schoolEvent->end_date->format('Y-m-d'),
            ]);
        }
    }

    public function updated(SchoolEvent $schoolEvent): void
    {
        $this->calendarService->clearCache();
    }

    public function deleted(SchoolEvent $schoolEvent): void
    {
        $this->calendarService->clearCache();
    }
}
