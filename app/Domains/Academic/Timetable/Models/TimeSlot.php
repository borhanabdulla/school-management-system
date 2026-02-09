<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Models;

use App\Domains\Shared\Enums\DayOfWeek;
use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Infrastructure\Traits\HasModelLabels;
use Database\Factories\Domains\Academic\Timetable\Models\TimeSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TimeSlot extends Model
{
    use HasFactory, HasModelLabels;

    protected static function newFactory(): TimeSlotFactory
    {
        return TimeSlotFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'الفترة الزمنية';
    protected static string $modelPluralLabel = 'الفترات الزمنية';
    protected static string $labelAttribute = 'label';

    protected $fillable = [
        'template_id',
        'day_of_week',
        'label',
        'order_index',
        'start_time',
        'end_time',
        'type',
        'is_attendance_checkpoint',
    ];

    /**
     * ⚠️  PHASE 5: Fixed casts
     * 
     * Previously used `datetime:H:i` which is incorrect - datetime cast expects
     * a full datetime string, not a time format. Using 'immutable:H:i' or
     * custom casting for time-only fields.
     * 
     * Using 'string' for time fields since they represent time-of-day values
     * that should be stored and retrieved as strings (format: HH:MM).
     */
    protected $casts = [
        'day_of_week' => 'integer',
        'order_index' => 'integer',
        'start_time' => 'string', // Format: HH:MM (e.g., "07:00")
        'end_time' => 'string',   // Format: HH:MM (e.g., "07:45")
        'type' => TimeSlotType::class,
        'is_attendance_checkpoint' => 'boolean',
    ];

    // ==================== العلاقات ====================

    public function template(): BelongsTo
    {
        return $this->belongsTo(TimetableTemplate::class, 'template_id');
    }

    /**
     * الحصص المرتبطة بهذا الوقت
     *
     * @note This returns ALL timetables using this slot.
     * Use timetableEntriesForTerm() to filter by specific term.
     */
    public function timetableEntries(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * الحصص المرتبطة بهذا الوقت في ترم محدد
     *
     * ⚠️  PHASE 3: Added for better query filtering
     *
     * @param int $termId The term ID to filter by
     * @return HasMany
     */
    public function timetableEntriesForTerm(int $termId): HasMany
    {
        return $this->hasMany(Timetable::class)
            ->where('term_id', $termId);
    }

    /**
     * التحقق من وجود حصص مرتبطة في ترم محدد
     *
     * @param int $termId
     * @return bool
     */
    public function hasEntriesInTerm(int $termId): bool
    {
        return $this->timetableEntriesForTerm($termId)->exists();
    }

    // ==================== Scopes ====================

    /**
     * حصص يوم معين
     */
    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * الحصص الدراسية فقط
     */
    public function scopeAcademic($query)
    {
        return $query->where('type', TimeSlotType::Academic);
    }

    /**
     * الاستراحات فقط
     */
    public function scopeBreaks($query)
    {
        return $query->where('type', TimeSlotType::Break);
    }

    /**
     * الأنواع القابلة للتعيين (دراسية ونشاط)
     */
    public function scopeAssignable($query)
    {
        return $query->whereIn('type', [TimeSlotType::Academic, TimeSlotType::Activity]);
    }

    /**
     * مرتبة حسب الترتيب
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    // ==================== Accessors ====================

    /**
     * الفترة الزمنية منسقة
     * 
     * @return string|null Format: "07:00 - 07:45" or null if times are not set
     */
    public function getTimeRangeAttribute(): ?string
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }
        return "{$this->start_time} - {$this->end_time}";
    }

    /**
     * اسم اليوم
     */
    public function getDayNameAttribute(): string
    {
        return DayOfWeek::tryFrom($this->day_of_week)?->label() ?? '';
    }

    /**
     * اختصار اليوم
     */
    public function getDayShortNameAttribute(): string
    {
        return DayOfWeek::tryFrom($this->day_of_week)?->shortLabel() ?? '';
    }

    /**
     * مدة الحصة بالدقائق
     * 
     * @return int Duration in minutes, 0 if times are not set or invalid
     */
    public function getDurationMinutesAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        try {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            return (int) $start->diffInMinutes($end);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // ==================== Helpers ====================

    /**
     * هل الحصة قابلة للتعيين (معلم + مادة)؟
     */
    public function isAssignable(): bool
    {
        return $this->type?->isAssignable() ?? false;
    }

    /**
     * هل تحتسب ضمن نصاب المعلم؟
     */
    public function countsForTeacherLoad(): bool
    {
        return $this->type?->countsForTeacherLoad() ?? false;
    }

    /**
     * هل هناك تداخل مع حصة أخرى؟
     */
    public function overlapsWithAnother(TimeSlot $other): bool
    {
        if ($this->day_of_week !== $other->day_of_week) {
            return false;
        }

        if (!$this->start_time || !$this->end_time || !$other->start_time || !$other->end_time) {
            return false;
        }

        try {
            $thisStart = Carbon::parse($this->start_time);
            $thisEnd = Carbon::parse($this->end_time);
            $otherStart = Carbon::parse($other->start_time);
            $otherEnd = Carbon::parse($other->end_time);

            return $thisStart < $otherEnd && $thisEnd > $otherStart;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get start time as Carbon instance
     * 
     * @param string|null $date Reference date for creating Carbon instance
     * @return Carbon|null
     */
    public function getStartTimeCarbon(?string $date = null): ?Carbon
    {
        if (!$this->start_time) {
            return null;
        }

        try {
            $date = $date ?? now()->toDateString();
            return Carbon::parse("{$date} {$this->start_time}");
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get end time as Carbon instance
     * 
     * @param string|null $date Reference date for creating Carbon instance
     * @return Carbon|null
     */
    public function getEndTimeCarbon(?string $date = null): ?Carbon
    {
        if (!$this->end_time) {
            return null;
        }

        try {
            $date = $date ?? now()->toDateString();
            return Carbon::parse("{$date} {$this->end_time}");
        } catch (\Throwable $e) {
            return null;
        }
    }
}
