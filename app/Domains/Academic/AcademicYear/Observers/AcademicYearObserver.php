<?php

namespace App\Domains\Academic\AcademicYear\Observers;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService;
use Illuminate\Support\Facades\Cache;

class AcademicYearObserver
{
    public function __construct(
        protected AcademicYearLookupService $lookupService
    ) {
    }

    /**
     * مسح الكاش عند أي تغيير
     */
    protected function clearCache(): void
    {
        // استخدام السيرفس كمرجع وحيد لإبطال الكاش
        $this->lookupService->invalidateCache();

        // ✨ مسح جميع الكاش المرتبط بالنطاق الأكاديمي (إذا كان مدعوماً)
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggedCache || method_exists(Cache::store(), 'tags')) {
                Cache::tags(['academic'])->flush();
            }
        } catch (\Exception $e) {
            // Silently fail if tags not supported
        }
    }

    /**
     * بعد الإنشاء
     */
    public function created(AcademicYear $year): void
    {
        $this->clearCache();
    }

    /**
     * بعد التحديث
     */
    public function updated(AcademicYear $year): void
    {
        $this->clearCache();
        
        // ✅ Performance: Clear SchoolCalendarService cache if weekend_days changed
        if ($year->wasChanged('weekend_days')) {
            app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class)->clearCache();
        }
    }

    /**
     * بعد الحذف
     */
    public function deleted(AcademicYear $year): void
    {
        $this->clearCache();
    }

    /**
     * بعد الاستعادة (Soft Delete)
     */
    public function restored(AcademicYear $year): void
    {
        $this->clearCache();
    }
}
