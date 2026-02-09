<?php

namespace App\Domains\Academic\Timetable\Models;

use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasAcademicScope;
use App\Infrastructure\Traits\HasModelLabels;
use Database\Factories\Domains\Academic\Timetable\Models\TimetableTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;

class TimetableTemplate extends Model
{
    use HasFactory, HandlesSafeDelete, HasAcademicScope, HasModelLabels;

    protected static function newFactory(): TimetableTemplateFactory
    {
        return TimetableTemplateFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'قالب الجدول';
    protected static string $modelPluralLabel = 'قوالب الجداول';
    protected static string $labelAttribute = 'name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'timeSlots' => 'الحصص الزمنية',
        'grades' => 'الصفوف المرتبطة',
    ];

    protected $fillable = [
        'name',
        'description',
        'working_days',
        'is_default',
        'status',
        'academic_year_id',
        'educational_stage_id',
    ];

    protected $casts = [
        'working_days' => 'array',
        'is_default' => 'boolean',
        'status' => TemplateStatus::class,
    ];

    // ==================== العلاقات ====================

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function educationalStage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class, 'template_id');
    }

    /**
     * الصفوف المعينة لهذا القالب
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'grade_timetable_template', 'template_id', 'grade_id')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    // ==================== Scopes ====================

    /**
     * القوالب النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('status', TemplateStatus::Active);
    }

    /**
     * القوالب المسودة
     */
    public function scopeDraft($query)
    {
        return $query->where('status', TemplateStatus::Draft);
    }

    /**
     * القالب الافتراضي
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * قوالب سنة دراسية محددة
     */
    public function scopeForYear($query, int $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    /**
     * قوالب مرحلة دراسية محددة
     */
    public function scopeForStage($query, int $stageId)
    {
        return $query->where('educational_stage_id', $stageId);
    }

    // ==================== Helpers ====================

    /**
     * جلب الحصص ليوم معين
     */
    public function getSlotsForDay(int $dayOfWeek)
    {
        return $this->timeSlots()
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('order_index')
            ->get();
    }

    /**
     * جلب أسماء الأيام العاملة
     */
    public function getWorkingDayLabelsAttribute(): array
    {
        return collect($this->working_days ?? [])
            ->map(fn($day) => DayOfWeek::tryFrom($day)?->label() ?? $day)
            ->toArray();
    }

    /**
     * هل اليوم ضمن أيام العمل؟
     */
    public function isWorkingDay(int $dayOfWeek): bool
    {
        $dayKey = DayOfWeek::from($dayOfWeek)->value;
        return in_array($dayKey, $this->working_days ?? []);
    }

    /**
     * هل القالب قابل للتعديل؟
     */
    public function isEditable(): bool
    {
        return $this->status?->isEditable() ?? true;
    }

    /**
     * هل القالب قابل للاستخدام في الجدول؟
     */
    public function isUsable(): bool
    {
        return $this->status?->isUsable() ?? false;
    }

    /**
     * عدد الحصص الدراسية (academic) في اليوم
     */
    public function getAcademicSlotsCountAttribute(): int
    {
        // نحسب من أول يوم فقط (لأن كل الأيام متشابهة غالباً)
        $firstDay = $this->working_days[0] ?? 0;
        return $this->timeSlots()
            ->where('day_of_week', is_string($firstDay) ? DayOfWeek::from($firstDay)->value : $firstDay)
            ->where('type', 'academic')
            ->count();
    }
}
