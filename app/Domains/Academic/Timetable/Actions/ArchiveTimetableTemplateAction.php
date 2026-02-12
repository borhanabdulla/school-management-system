<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Support\Facades\Log;

/**
 * ArchiveTimetableTemplateAction - أرشفة قالب جدول
 */
class ArchiveTimetableTemplateAction
{
    public function execute(TimetableTemplate $template): void
    {
        app(AcademicWriteGuard::class)->assertYearNotClosed($template->academic_year_id);

        $template->update(['status' => TemplateStatus::Archived]);

        Log::info('Timetable template archived', ['id' => $template->id]);
    }
}
