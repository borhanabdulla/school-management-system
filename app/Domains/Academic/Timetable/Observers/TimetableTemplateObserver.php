<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Observers;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Services\TimetableLookupService;

/**
 * TimetableTemplateObserver - مراقب تغييرات قوالب الجدول
 * 
 * متى نستخدم Observer بدلاً من Event؟
 * - Observer: للعمليات التلقائية المرتبطة بدورة حياة الموديل (created, updated, deleted)
 *   مثل: إبطال الكاش، تحديث الإحصائيات، التسجيل في Audit Log
 * 
 * - Event: للعمليات التي تحتاج لـ Listeners متعددين أو قابلة للتوسع
 *   مثل: إرسال إشعارات، تكامل مع أنظمة خارجية
 * 
 * هذا الـ Observer يُستخدم لإبطال الكاش تلقائياً عند أي تغيير.
 */
class TimetableTemplateObserver
{
    public function __construct(
        protected TimetableLookupService $lookupService
    ) {
    }

    /**
     * بعد الإنشاء
     */
    public function created(TimetableTemplate $template): void
    {
        $this->invalidateCache();
    }

    /**
     * بعد التحديث
     */
    public function updated(TimetableTemplate $template): void
    {
        $this->invalidateCache();
    }

    /**
     * بعد الحذف
     */
    public function deleted(TimetableTemplate $template): void
    {
        $this->invalidateCache();
    }

    /**
     * إبطال الكاش
     */
    private function invalidateCache(): void
    {
        $this->lookupService->invalidateCache();
    }
}
