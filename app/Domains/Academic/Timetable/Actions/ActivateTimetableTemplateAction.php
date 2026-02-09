<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Exceptions\InvalidTimeSlotsException;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use Illuminate\Support\Facades\Log;

/**
 * ActivateTimetableTemplateAction - تفعيل قالب جدول
 */
class ActivateTimetableTemplateAction
{
    public function execute(TimetableTemplate $template): void
    {
        if ($template->status === TemplateStatus::Active) {
            return;
        }

        // التحقق من وجود حصص
        if ($template->timeSlots()->count() === 0) {
            throw InvalidTimeSlotsException::noSlotsProvided();
        }

        $template->update(['status' => TemplateStatus::Active]);

        Log::info('Timetable template activated', ['id' => $template->id]);
    }
}
