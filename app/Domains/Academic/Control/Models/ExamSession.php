<?php

namespace App\Domains\Academic\Control\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasAcademicScope;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;

class ExamSession extends Model
{
    use HasFactory, HandlesSafeDelete, HasAcademicScope, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'دورة الامتحانات';
    protected static string $modelPluralLabel = 'دورات الامتحانات';
    protected static string $labelAttribute = 'name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'seatings' => 'أرقام الجلوس',
        'committees' => 'اللجان',
        'finalResults' => 'النتائج النهائية',
    ];
    protected $fillable = [
        'academic_year_id',
        'term_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'status' => \App\Domains\Academic\Control\Enums\ExamSessionStatus::class,
    ];

    // ==================== العلاقات ====================

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function committees(): HasMany
    {
        return $this->hasMany(ExamCommittee::class);
    }

    public function seatings(): HasMany
    {
        return $this->hasMany(ExamSeating::class);
    }

    public function finalResults(): HasMany
    {
        return $this->hasMany(FinalResult::class);
    }

    public function marks()
    {
        return $this->hasManyThrough(ControlMark::class, ExamSeating::class);
    }

    // ==================== الحالات ====================

    public function isSetup(): bool
    {
        return $this->status === \App\Domains\Academic\Control\Enums\ExamSessionStatus::Setup;
    }

    public function isActive(): bool
    {
        return $this->status === \App\Domains\Academic\Control\Enums\ExamSessionStatus::Active;
    }

    public function isProcessing(): bool
    {
        return $this->status === \App\Domains\Academic\Control\Enums\ExamSessionStatus::Processing;
    }

    public function isPublished(): bool
    {
        return $this->status === \App\Domains\Academic\Control\Enums\ExamSessionStatus::Published;
    }

    public function isClosed(): bool
    {
        return $this->status === \App\Domains\Academic\Control\Enums\ExamSessionStatus::Closed;
    }

    // ==================== الإحصائيات ====================

    public function getStudentsCountAttribute(): int
    {
        if (array_key_exists('students_count', $this->attributes)) {
            return $this->attributes['students_count'];
        }
        return $this->seatings()->count();
    }

    public function getMarksEnteredCountAttribute(): int
    {
        if (array_key_exists('marks_entered_count', $this->attributes)) {
            return $this->attributes['marks_entered_count'];
        }
        return ControlMark::whereHas('seating', fn($q) => $q->where('exam_session_id', $this->id))
            ->whereNotNull('score')
            ->count();
    }
}
