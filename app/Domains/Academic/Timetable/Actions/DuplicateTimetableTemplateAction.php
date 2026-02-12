<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DuplicateTimetableTemplateAction - نسخ قالب جدول
 */
class DuplicateTimetableTemplateAction
{
    public function execute(TimetableTemplate $template, string $newName): TimetableTemplate
    {
        app(AcademicWriteGuard::class)->assertYearNotClosed($template->academic_year_id);

        return DB::transaction(function () use ($template, $newName) {
            // نسخ القالب
            $newTemplate = $template->replicate();
            $newTemplate->name = $newName;
            $newTemplate->status = TemplateStatus::Draft;
            $newTemplate->is_default = false;
            $newTemplate->save();

            // نسخ الحصص
            foreach ($template->timeSlots as $slot) {
                $newSlot = $slot->replicate();
                $newSlot->template_id = $newTemplate->id;
                $newSlot->save();
            }

            Log::info('Timetable template duplicated', [
                'original_id' => $template->id,
                'new_id' => $newTemplate->id
            ]);

            return $newTemplate->fresh(['timeSlots']);
        });
    }
}
