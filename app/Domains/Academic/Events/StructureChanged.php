<?php

declare(strict_types=1);

namespace App\Domains\Academic\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * StructureChanged - حدث تغيير الهيكل الأكاديمي
 * 
 * يتم إطلاقه عند:
 * - إضافة/حذف مرحلة دراسية
 * - إضافة/حذف صف
 * - إضافة/حذف شعبة
 * 
 * مفيد لتحديث الكاش المرتبط بالهيكل (Menu, Dropdowns, etc.)
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class StructureChanged
{
    use Dispatchable, SerializesModels;

    /**
     * نوع التغيير
     */
    public const TYPE_STAGE = 'stage';
    public const TYPE_GRADE = 'grade';
    public const TYPE_SECTION = 'section';

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';

    /**
     * إنشاء الحدث
     * 
     * @param string $type نوع العنصر الذي تغير
     * @param string $action نوع العملية (created, updated, deleted)
     * @param mixed $model الموديل الذي تغير
     */
    public function __construct(
        public readonly string $type,
        public readonly string $action,
        public readonly mixed $model,
    ) {
    }
}
