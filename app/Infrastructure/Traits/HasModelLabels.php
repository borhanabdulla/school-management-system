<?php

declare(strict_types=1);

namespace App\Infrastructure\Traits;

/**
 * HasModelLabels - ترجمة أسماء الموديلات
 * 
 * هذا الـ Trait يوفر أسماء عربية للموديلات لاستخدامها في:
 * - رسائل الخطأ
 * - عناوين الصفحات
 * - الإشعارات
 * - سجلات المراجعة (Audit Logs)
 * 
 * @example
 * class Student extends Model {
 *     use HasModelLabels;
 *     
 *     protected static string $modelLabel = 'طالب';
 *     protected static string $modelPluralLabel = 'طلاب';
 * }
 * 
 * // الاستخدام
 * Student::getModelLabel();       // "طالب"
 * Student::getModelPluralLabel(); // "طلاب"
 * $student->getLabel();           // "الطالب: أحمد محمد"
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
trait HasModelLabels
{
    /**
     * الحصول على اسم الموديل بالعربي (مفرد)
     * 
     * @return string
     */
    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? class_basename(static::class);
    }

    /**
     * الحصول على اسم الموديل بالعربي (جمع)
     * 
     * @return string
     */
    public static function getModelPluralLabel(): string
    {
        return static::$modelPluralLabel ?? static::getModelLabel();
    }

    /**
     * الحصول على وصف السجل (للعرض في الواجهات)
     * 
     * يمكن تخصيصه في الموديل عبر تعريف $labelAttribute
     * 
     * @return string
     */
    public function getLabel(): string
    {
        $attribute = static::$labelAttribute ?? 'name';
        $value = $this->{$attribute} ?? $this->getKey();

        return static::getModelLabel() . ': ' . $value;
    }

    /**
     * الحصول على اسم السجل فقط (بدون نوع الموديل)
     * 
     * @return string
     */
    public function getDisplayName(): string
    {
        $attribute = static::$labelAttribute ?? 'name';
        return $this->{$attribute} ?? (string) $this->getKey();
    }
}
