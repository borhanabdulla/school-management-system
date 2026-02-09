<?php

declare(strict_types=1);

namespace App\Domains\Academic\AcademicYear\Events;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * AcademicYearActivated - حدث تفعيل سنة دراسية
 * 
 * يتم إطلاقه عند تفعيل سنة دراسية جديدة.
 * يمكن استخدامه لـ:
 * - إرسال إشعارات للموظفين
 * - تحديث الإحصائيات
 * - إعادة تهيئة بعض الخدمات
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class AcademicYearActivated
{
    use Dispatchable, SerializesModels;

    /**
     * إنشاء الحدث
     * 
     * @param AcademicYear $year السنة التي تم تفعيلها
     * @param AcademicYear|null $previousYear السنة السابقة (إن وجدت)
     */
    public function __construct(
        public readonly AcademicYear $year,
        public readonly ?AcademicYear $previousYear = null,
    ) {
    }
}
