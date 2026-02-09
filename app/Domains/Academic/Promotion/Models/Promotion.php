<?php

namespace App\Domains\Academic\Promotion\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Shared\Models\User;

class Promotion extends Model
{
    use HasFactory, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'قرار الترحيل';
    protected static string $modelPluralLabel = 'قرارات الترحيل';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'annual_result_id',
        'from_grade_id',
        'to_grade_id',
        'to_class_section_id',
        'type',
        'has_financial_clearance',
        'certificate_blocked',
        'is_reverted',
        'reverted_by',
        'reverted_at',
        'revert_reason',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'has_financial_clearance' => 'boolean',
        'certificate_blocked' => 'boolean',
        'is_reverted' => 'boolean',
        'reverted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // ==================== العلاقات ====================

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function annualResult(): BelongsTo
    {
        return $this->belongsTo(AnnualResult::class);
    }

    public function fromGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'from_grade_id');
    }

    public function toGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'to_grade_id');
    }

    public function toClassSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'to_class_section_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function revertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    // ==================== الحالات ====================

    public function isPromoted(): bool
    {
        return $this->type === 'promoted';
    }

    public function isRepeated(): bool
    {
        return $this->type === 'repeated';
    }

    public function isGraduated(): bool
    {
        return $this->type === 'graduated';
    }

    public function isActive(): bool
    {
        return !$this->is_reverted;
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_reverted', false);
    }

    public function scopeReverted($query)
    {
        return $query->where('is_reverted', true);
    }

    public function scopeForYear($query, int $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    public function scopePromoted($query)
    {
        return $query->where('type', 'promoted');
    }

    public function scopeRepeated($query)
    {
        return $query->where('type', 'repeated');
    }
}
