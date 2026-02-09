<?php

namespace App\Domains\Academic\Timetable\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Database\Factories\Domains\Academic\Timetable\Models\TimetableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Attendance\Models\Attendance;

class Timetable extends Model
{
    use HasFactory, HasModelLabels;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\InvalidatesCache;
    use \App\Infrastructure\Traits\HasAcademicScope;

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'attendances' => 'سجلات حضور',
        'substitutions' => 'بدائل',
    ];

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['timetable'];
    protected bool $autoFillAcademicYear = false;

    protected static function newFactory(): TimetableFactory
    {
        return TimetableFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'حصة الجدول';
    protected static string $modelPluralLabel = 'حصص الجدول';

    protected $fillable = [
        'class_section_id',
        'time_slot_id',
        'course_offering_id',
        'term_id', // ✅ PR0: تمييز الجدول بالترم
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Academic\Term\Models\Term::class);
    }

    // تعمل هذه العلاقه على الربط بين الجدول الدراسي وال Offering   نستفيد منها مثلا عند  جلب المادة مباشرة   
    // مثال على ذلك   
    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    /**
     * ✅ PR-1: الحضور المرتبط بهذه الحصة
     * يستخدمها HandlesSafeDelete للحماية من الحذف
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'timetable_id');
    }

    /**
     * ✅ PR-1: البدائل المرتبطة بهذه الحصة
     * يستخدمها HandlesSafeDelete للحماية من الحذف
     */
    public function substitutions(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Substitution\Models\Substitution::class, 'timetable_id');
    }

    /**
     * جلب المادة مباشرة
     */
    public function getSubjectAttribute()
    {
        return $this->courseOffering?->subject;
    }

    /**
     * جلب المعلم مباشرة
     */
    public function getTeacherAttribute()
    {
        return $this->courseOffering?->teacher;
    }

    /**
     * التحقق من تعارض المعلم
     * ✅ PR-3: إضافة فلتر السنة الدراسية
     */
    public static function hasTeacherConflict(int $teacherId, int $timeSlotId, int $academicYearId, ?int $excludeId = null): bool
    {
        $query = static::whereHas('courseOffering', fn($q) => $q->where('teacher_id', $teacherId)->where('academic_year_id', $academicYearId))
            ->where('time_slot_id', $timeSlotId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * ✅ PR0: Scope لفلترة الجدول حسب الترم
     */
    public function scopeForTerm($query, int $termId)
    {
        return $query->where('term_id', $termId);
    }
}
