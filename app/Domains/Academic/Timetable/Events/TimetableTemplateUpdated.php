<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Events;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * TimetableTemplateUpdated - حدث تحديث قالب جدول
 */
class TimetableTemplateUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TimetableTemplate $template
    ) {
    }
}
