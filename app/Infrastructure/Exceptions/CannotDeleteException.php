<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use Exception;

/**
 * CannotDeleteException - استثناء عدم إمكانية الحذف
 * 
 * يُرمى عند محاولة حذف سجل له علاقات مرتبطة.
 * يوفر رسالة واضحة للمستخدم بالعربي.
 * 
 * @example
 * throw new CannotDeleteException('لا يمكن حذف الصف لوجود 25 طالب');
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class CannotDeleteException extends Exception
{
    /**
     * العلاقات التي تمنع الحذف
     */
    protected array $blockers = [];

    /**
     * إنشاء استثناء جديد
     * 
     * @param string $message رسالة الخطأ
     * @param array $blockers العلاقات المانعة
     */
    public function __construct(string $message = 'لا يمكن حذف هذا السجل لوجود بيانات مرتبطة', array $blockers = [])
    {
        parent::__construct($message);
        $this->blockers = $blockers;
    }

    /**
     * الحصول على العلاقات المانعة
     * 
     * @return array
     */
    public function getBlockers(): array
    {
        return $this->blockers;
    }

    /**
     * إنشاء استثناء بقائمة العلاقات
     * 
     * @param string $modelLabel اسم الموديل
     * @param array $blockers العلاقات مع أعدادها
     * @return static
     */
    public static function forRelations(string $modelLabel, array $blockers): static
    {
        $message = "لا يمكن حذف {$modelLabel} لوجود بيانات مرتبطة:\n";
        $message .= implode("\n", array_map(fn($b) => "• {$b}", $blockers));

        return new static($message, $blockers);
    }
}
