<?php

namespace App\Domains\Academic\Calendar\Models;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Infrastructure\Traits\HasAcademicScope;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEvent extends Model
{
    use HasAcademicScope, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'الحدث المدرسي';
    protected static string $modelPluralLabel = 'الأحداث المدرسية';
    protected static string $labelAttribute = 'title';

    protected $fillable = [
        'academic_year_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'type',
        'is_holiday',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_holiday' => 'boolean',
    ];

    /**
     * العلاقة مع السنة الدراسية
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope: أحداث السنة الحالية
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereHas('academicYear', fn($q) => $q->where('status', AcademicYearStatus::Active));
    }

    /**
     * Scope: العطل فقط
     */
    public function scopeHolidays($query)
    {
        return $query->where('is_holiday', true);
    }

    /**
     * Scope: أحداث في نطاق تاريخ معين
     */
    public function scopeInRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * هل هذا التاريخ ضمن الحدث؟
     */
    public function containsDate($date): bool
    {
        $date = \Carbon\Carbon::parse($date)->startOfDay();
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * عدد أيام الحدث
     */
    public function getDaysCountAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * لون الحدث حسب النوع (للواجهة)
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'holiday' => 'blue',
            'emergency' => 'red',
            'exam' => 'orange',
            'activity' => 'green',
            default => 'gray',
        };
    }

    /**
     * اسم النوع بالعربية
     */
    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'holiday' => 'عطلة رسمية',
            'emergency' => 'حالة طارئة',
            'exam' => 'فترة اختبارات',
            'activity' => 'نشاط مدرسي',
            default => 'غير محدد',
        };
    }
}
