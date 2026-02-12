<?php

namespace App\Domains\Academic\AcademicYear\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;


class AcademicYear extends Model
{
    use HasFactory;
    use HandlesSafeDelete;
    use HasModelLabels;
    use InvalidatesCache;

    protected static function newFactory()
    {
        return \Database\Factories\AcademicYearFactory::new();
    }

    /**
     * اسم الموديل بالعربي
     */
    protected static string $modelLabel = 'سنة دراسية';
    protected static string $modelPluralLabel = 'سنوات دراسية';

    /**
     * العلاقات المحمية من الحذف
     * تمنع الحذف عند وجود بيانات تاريخية مرتبطة بالسنة
     */
    protected array $protectedRelations = [
        'enrollments' => 'تسجيلات طلاب',
    ];

    /**
     * Cache Tags
     */
    protected array $cacheTags = ['academic', 'years'];

    protected $fillable = ['name', 'start_date', 'end_date', 'status', 'weekend_days', 'financial_status', 'financial_closed_at', 'financial_closed_by'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => AcademicYearStatus::class,
        'financial_closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $year) {
            if ($year->weekend_days === null) {
                $year->weekend_days = json_encode([5, 6]);
            }
        });
    }

    // Relationships
    public function terms(): HasMany // ارتباط بين العام الدراسي والترم 
    {
        return $this->hasMany(Term::class);
    }
    // علاقة: السنة لها شعب كثيرة
    public function sections()
    {
        return $this->hasMany(ClassSection::class);
    }

    public function students()
    {
        return $this->hasManyThrough(
            \App\Domains\Academic\Student\Models\Student::class,      // الموديل النهائي (الطلاب)
            \App\Domains\Academic\ClassSection\Models\ClassSection::class, // الموديل الوسيط (الأقسام الدراسية)
            'academic_year_id',              // المفتاح الأجنبي في جدول الأقسام (class_sections)
            'current_class_section_id',      // المفتاح الأجنبي الصحيح في جدول الطلاب (students)
            'id',                            // المفتاح المحلي في جدول السنوات
            'id'                             // المفتاح المحلي في جدول الأقسام
        );
    }

    // علاقة: تسجيلات الطلاب التاريخية لهذه السنة
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // علاقة: الطلاب المسجلون في هذه السنة عبر enrollments (ثابتة تاريخياً)
    public function enrolledStudents(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'student_enrollments',
            'academic_year_id',
            'student_id'
        );
    }

    // Scopes
    public function scopeActive($query) // تسهيل الاستعلام 
    {
        return $query->where('status', AcademicYearStatus::Active);
    }

    public function scopePending($query) // البحث عن العام الدراسي في حالة الانتظار
    {
        return $query->where('status', AcademicYearStatus::Pending);
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('start_date', 'desc');
    }

    public function scopeWithStats($query)
    {
        return $query->withCount(['terms', 'students', 'enrollments']);
    }

    // ══════════════════════════════════════════════════════════════
    // State Check Helpers (Lifecycle Helpers)
    // ══════════════════════════════════════════════════════════════

    /**
     * هل السنة نشطة حالياً؟
     */
    public function isActive(): bool
    {
        return $this->status === AcademicYearStatus::Active;
    }

    /**
     * هل السنة قادمة (في المستقبل)؟
     */
    public function isIncoming(): bool
    {
        return $this->start_date->isFuture();
    }

    /**
     * هل السنة للقراءة فقط (مغلقة أو مؤرشفة)؟
     */
    public function isReadOnly(): bool
    {
        return in_array($this->status, [
            AcademicYearStatus::Closed,
            AcademicYearStatus::Archived
        ]);
    }

    /**
     * هل السنة منتهية (في الماضي)؟
     */
    public function isPast(): bool
    {
        return $this->end_date->isPast();
    }

    /**
     * هل نحن داخل نطاق هذه السنة الآن؟
     */
    public function isCurrent(): bool
    {
        return now()->between($this->start_date, $this->end_date);
    }

    /**
     * حساب نسبة التقدم في السنة الدراسية
     */
    public function getProgressPercentage(): float
    {
        $now = now();

        if ($now->lt($this->start_date))
            return 0;
        if ($now->gt($this->end_date))
            return 100;

        $total = $this->start_date->diffInDays($this->end_date);
        $passed = $this->start_date->diffInDays($now);

        return $total > 0 ? round(($passed / $total) * 100, 2) : 0;
    }

    // ══════════════════════════════════════════════════════════════
    // Business Rule Helpers
    // ══════════════════════════════════════════════════════════════

    /**
     * هل السنة مؤرشفة؟
     */
    public function isArchived(): bool
    {
        return $this->status === AcademicYearStatus::Archived;
    }

    /**
     * هل يمكن تعديل التواريخ؟ (فقط المسودة)
     */
    public function canEditDates(): bool
    {
        return $this->status === AcademicYearStatus::Pending;
    }

    /**
     * هل يمكن تعديل الهيكل (الفصول)؟ (فقط المسودة)
     */
    public function canEditStructure(): bool
    {
        return $this->status === AcademicYearStatus::Pending;
    }

    /**
     * هل يمكن تعديل الاسم؟ (مسودة، نشطة، مغلقة)
     */
    public function canEditName(): bool
    {
        return in_array($this->status, [
            AcademicYearStatus::Pending,
            AcademicYearStatus::Active,
            AcademicYearStatus::Closed
        ]);
    }

    /**
     * هل يمكن أرشفة السنة؟ (فقط المغلقة)
     */
    public function canBeArchived(): bool
    {
        return $this->status === AcademicYearStatus::Closed;
    }

    /**
     * هل السنة جاهزة للتفعيل؟
     */
    public function isReadyForActivation(): bool
    {
        $count = $this->terms_count ?? $this->terms()->count();
        return $this->status === AcademicYearStatus::Pending && $count >= 2;
    }

    /**
     * هل يمكن حذف السنة؟
     */
    public function canBeDeleted(): bool
    {
        $enrollmentCount = $this->enrollments_count ?? $this->enrollments()->count();

        // الحذف مسموح فقط للسنة المسودة (Pending) بشرط عدم وجود تسجيلات
        return $this->status === AcademicYearStatus::Pending && $enrollmentCount === 0;
    }

    /**
     * هل يمكن تعديل السنة بالكامل؟ (فقط في حالة المسودة)
     */
    public function canBeFullyEdited(): bool
    {
        return $this->status === AcademicYearStatus::Pending;
    }

    /**
     * هل يمكن تعديل الاسم فقط؟ (السنة النشطة أو المغلقة)
     * @deprecated Use canEditName() instead
     */
    public function canEditNameOnly(): bool
    {
        return in_array($this->status, [
            AcademicYearStatus::Active,
            AcademicYearStatus::Closed
        ]);
    }

    /**
     * هل تحتوي على الحد الأدنى من الفصول (2)؟
     */
    public function hasMinimumTerms(): bool
    {
        $count = $this->terms_count ?? $this->terms()->count();
        return $count >= 2;
    }

    /**
     * هل يمكن إغلاق السنة الدراسية؟
     * يجب أن تكون السنة نشطة (Active)
     */
    public function canBeClosed(): bool
    {
        return $this->status === AcademicYearStatus::Active;
    }


    /**
     * Get promotions from this year
     */
    public function promotions()
    {
        return $this->hasMany(\App\Domains\Academic\Promotion\Models\Promotion::class);
    }

    /**
     * Get annual results for this year
     */
    public function annualResults()
    {
        return $this->hasMany(\App\Domains\Academic\Results\Models\AnnualResult::class);
    }

    /**
     * Get fee structures for this year
     */
    public function feeStructures()
    {
        return $this->hasMany(\App\Domains\Finance\Models\FeeStructure::class);
    }

    /**
     * Get course offerings for this year
     */
    public function courseOfferings()
    {
        return $this->hasMany(\App\Domains\Academic\CourseOffering\Models\CourseOffering::class);
    }

    /**
     * Get timetable templates for this year
     */
    public function timetableTemplates()
    {
        return $this->hasMany(\App\Domains\Academic\Timetable\Models\TimetableTemplate::class);
    }

    /**
     * Get school events for this year
     */
    public function schoolEvents()
    {
        return $this->hasMany(\App\Domains\Academic\Calendar\Models\SchoolEvent::class);
    }
}
