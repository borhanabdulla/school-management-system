<?php

declare(strict_types=1);

namespace App\Infrastructure\Traits;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Exceptions\CannotDeleteException;

/**
 * HandlesSafeDelete - حماية من الحذف الخاطئ
 * 
 * هذا الـ Trait يمنع حذف السجلات التي لديها علاقات مرتبطة.
 * يتحقق من العلاقات المحمية قبل الحذف ويرمي استثناء واضح.
 * 
 * @example
 * class Grade extends Model {
 *     use HandlesSafeDelete;
 *     
 *     // العلاقات التي يجب التحقق منها قبل الحذف
 *     protected array $protectedRelations = ['classSections', 'students'];
 * }
 * 
 * // الآن عند محاولة الحذف:
 * $grade->delete(); // سيرمي CannotDeleteException إذا كان هناك طلاب
 * 
 * // للتحقق قبل الحذف:
 * if ($grade->canDelete()) {
 *     $grade->delete();
 * }
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
trait HandlesSafeDelete
{
    /**
     * تفعيل حماية الحذف
     * 
     * يُستدعى تلقائياً عند تحميل الـ Trait
     */
    public static function bootHandlesSafeDelete(): void
    {
        static::deleting(function (Model $model) {
            $blockers = $model->getDeletionBlockers();

            if (!empty($blockers)) {
                $modelLabel = method_exists($model, 'getModelLabel')
                    ? $model::getModelLabel()
                    : class_basename($model);

                $message = "لا يمكن حذف {$modelLabel} لوجود بيانات مرتبطة:\n";
                $message .= implode("\n", array_map(fn($b) => "• {$b}", $blockers));

                throw new CannotDeleteException($message);
            }
        });
    }

    /**
     * التحقق من إمكانية الحذف
     * 
     * @return bool
     */
    public function canDelete(): bool
    {
        return empty($this->getDeletionBlockers());
    }

    /**
     * الحصول على قائمة العلاقات التي تمنع الحذف
     * 
     * @return array<string> أسماء العلاقات مع عدد السجلات
     */
    public function getDeletionBlockers(): array
    {
        $blockers = [];

        foreach ($this->getProtectedRelations() as $relation => $label) {
            // دعم الصيغتين: ['relation' => 'label'] أو ['relation1', 'relation2']
            if (is_numeric($relation)) {
                $relation = $label;
                $label = $this->getRelationLabel($relation);
            }

            if (method_exists($this, $relation)) {
                $count = $this->{$relation}()->count();
                if ($count > 0) {
                    $blockers[] = "{$label} ({$count})";
                }
            }
        }

        return $blockers;
    }

    /**
     * الحصول على العلاقات المحمية
     * 
     * @return array
     */
    public function getProtectedRelations(): array
    {
        return $this->protectedRelations ?? [];
    }

    /**
     * الحصول على اسم العلاقة بالعربي
     * 
     * @param string $relation
     * @return string
     */
    protected function getRelationLabel(string $relation): string
    {
        $labels = [
            'students' => 'طلاب',
            'teachers' => 'معلمين',
            'staff' => 'موظفين',
            'classSections' => 'شعب دراسية',
            'sections' => 'أقسام',
            'terms' => 'فصول دراسية',
            'enrollments' => 'تسجيلات',
            'grades' => 'صفوف',
            'subjects' => 'مواد',
            'courseOfferings' => 'مقررات',
            'attendances' => 'سجلات حضور',
            'marks' => 'درجات',
            'homeworks' => 'واجبات',
            'payments' => 'مدفوعات',
            'invoices' => 'فواتير',
        ];

        return $labels[$relation] ?? $relation;
    }

    /**
     * الحصول على ملخص العلاقات المرتبطة
     * 
     * مفيد لعرض معلومات للمستخدم قبل الحذف
     * 
     * @return array<string, int>
     */
    public function getRelatedCounts(): array
    {
        $counts = [];

        foreach ($this->getProtectedRelations() as $relation => $label) {
            if (is_numeric($relation)) {
                $relation = $label;
                $label = $this->getRelationLabel($relation);
            }

            if (method_exists($this, $relation)) {
                $count = $this->{$relation}()->count();
                if ($count > 0) {
                    $counts[$label] = $count;
                }
            }
        }

        return $counts;
    }
}
