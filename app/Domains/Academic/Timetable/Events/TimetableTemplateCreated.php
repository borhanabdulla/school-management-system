<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Events;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * TimetableTemplateCreated - حدث إنشاء قالب جدول
 * 
 * يُطلق عند إنشاء قالب جديد. يمكن الاستماع إليه لـ:
 * - إرسال إشعارات
 * - تحديث الكاش
 * - تسجيل في Audit Log
 */
class TimetableTemplateCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TimetableTemplate $template
    ) {
    }
}
