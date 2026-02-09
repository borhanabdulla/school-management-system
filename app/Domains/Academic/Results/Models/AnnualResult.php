<?php

namespace App\Domains\Academic\Results\Models;

use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Infrastructure\Traits\HasAcademicScope;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\Promotion\Models\Promotion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domains\Academic\Results\Enums\ResultDecision;

class AnnualResult extends Model
{
    use HasFactory, HasAcademicScope, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'النتيجة السنوية';
    protected static string $modelPluralLabel = 'النتائج السنوية';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'grade_id',
        'term1_total',
        'term1_max',
        'term2_total',
        'term2_max',
        'annual_total',
        'annual_max',
        'percentage',
        'failed_subjects',
        'failed_count',
        'decision',
        'grade_label',
        'rank',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'decision' => ResultDecision::class,
        'term1_total' => 'decimal:2',
        'term1_max' => 'decimal:2',
        'term2_total' => 'decimal:2',
        'term2_max' => 'decimal:2',
        'annual_total' => 'decimal:2',
        'annual_max' => 'decimal:2',
        'percentage' => 'decimal:2',
        'failed_subjects' => 'array',
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

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function promotion(): HasOne
    {
        return $this->hasOne(Promotion::class);
    }

    // ==================== الحالات ====================

    public function isPending(): bool
    {
        return $this->decision === ResultDecision::Pending;
    }

    public function isPassed(): bool
    {
        return $this->decision === ResultDecision::Pass;
    }

    public function isConditional(): bool
    {
        return $this->decision === ResultDecision::Conditional;
    }

    public function isFailed(): bool
    {
        return $this->decision === ResultDecision::Fail;
    }

    public function canBePromoted(): bool
    {
        return in_array($this->decision, [ResultDecision::Pass, ResultDecision::Conditional]);
    }

    // ==================== التقديرات ====================

    public static function calculateGradeLabel(float $percentage): string
    {
        return app(GradingCalculatorService::class)->resolveGradeLabel($percentage);
    }

    // ==================== Scopes ====================

    public function scopeForYear($query, int $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    public function scopePending($query)
    {
        return $query->where('decision', ResultDecision::Pending);
    }

    public function scopePassed($query)
    {
        return $query->where('decision', ResultDecision::Pass);
    }

    public function scopeFailed($query)
    {
        return $query->where('decision', ResultDecision::Fail);
    }

    public function scopeConditional($query)
    {
        return $query->where('decision', ResultDecision::Conditional);
    }
}
