<?php

declare(strict_types=1);

namespace App\Infrastructure\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * HasAcademicScope - فلترة آلية بالسنة الدراسية
 * 
 * هذا الـ Trait يوفر:
 * 1. Scopes جاهزة للفلترة بالسنة والفصل الحالي
 * 2. تعبئة تلقائية لـ academic_year_id عند الإنشاء
 * 3. علاقة جاهزة مع AcademicYear
 * 
 * @example
 * class Enrollment extends Model {
 *     use HasAcademicScope;
 *     
 *     // تفعيل التعبئة التلقائية عند الإنشاء
 *     protected bool $autoFillAcademicYear = true;
 * }
 * 
 * // الاستخدام في الاستعلامات
 * Enrollment::currentYear()->get(); // فقط تسجيلات السنة الحالية
 * Enrollment::currentTerm()->get(); // فقط تسجيلات الفصل الحالي
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
trait HasAcademicScope
{
    /**
     * تفعيل التعبئة التلقائية
     */
    public static function bootHasAcademicScope(): void
    {
        static::creating(function (Model $model) {
            $activeYearId = $model->academic_year_id;

            // تعبئة academic_year_id تلقائياً إذا كانت فارغة
            if ($model->shouldAutoFillAcademicYear() && empty($model->academic_year_id)) {
                $activeYearId = school()->activeYearId();
                $model->academic_year_id = $activeYearId;
            }

            // تعبئة term_id تلقائياً إذا كانت فارغة وموجودة في الجدول
            if ($model->shouldAutoFillTerm() && $model->hasTerm() && empty($model->term_id)) {
                $activeTerm = school()->activeTerm();
                if ($activeTerm && (!$activeYearId || $activeTerm->academic_year_id === $activeYearId)) {
                    $model->term_id = $activeTerm->id;
                }
            }
        });
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * Scopes للفلترة
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * فلترة بالسنة الدراسية الحالية
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentYear(Builder $query): Builder
    {
        return $query->where('academic_year_id', school()->activeYearId());
    }

    /**
     * فلترة بالفصل الدراسي الحالي
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentTerm(Builder $query): Builder
    {
        return $query->where('term_id', school()->activeTermId());
    }

    /**
     * فلترة بسنة محددة
     * 
     * @param Builder $query
     * @param int $yearId
     * @return Builder
     */
    public function scopeForYear(Builder $query, int $yearId): Builder
    {
        return $query->where('academic_year_id', $yearId);
    }

    /**
     * فلترة بفصل محدد
     * 
     * @param Builder $query
     * @param int $termId
     * @return Builder
     */
    public function scopeForTerm(Builder $query, int $termId): Builder
    {
        return $query->where('term_id', $termId);
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * العلاقات
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * علاقة مع السنة الدراسية
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function academicYear()
    {
        return $this->belongsTo(\App\Domains\Academic\AcademicYear\Models\AcademicYear::class);
    }

    /**
     * علاقة مع الفصل الدراسي (إذا كان موجوداً)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function term()
    {
        if ($this->hasTerm()) {
            return $this->belongsTo(\App\Domains\Academic\Term\Models\Term::class);
        }
        return null;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * أدوات مساعدة
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * التحقق من أن السجل ينتمي للسنة الحالية
     * 
     * @return bool
     */
    public function isCurrentYear(): bool
    {
        return $this->academic_year_id === school()->activeYearId();
    }

    /**
     * التحقق من أن السجل ينتمي للفصل الحالي
     * 
     * @return bool
     */
    public function isCurrentTerm(): bool
    {
        return $this->term_id === school()->activeTermId();
    }

    /**
     * هل يجب التعبئة التلقائية للسنة؟
     * 
     * @return bool
     */
    protected function shouldAutoFillAcademicYear(): bool
    {
        return $this->autoFillAcademicYear ?? true;
    }

    /**
     * هل يجب التعبئة التلقائية للفصل؟
     * 
     * @return bool
     */
    protected function shouldAutoFillTerm(): bool
    {
        return $this->autoFillTerm ?? false;
    }

    /**
     * هل الموديل يحتوي على عمود term_id؟
     * 
     * @return bool
     */
    protected function hasTerm(): bool
    {
        return in_array('term_id', $this->getFillable()) ||
            $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'term_id');
    }
}
