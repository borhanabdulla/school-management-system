<?php

namespace App\Domains\Academic\Results\Models;

use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;

class FinalResult extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'النتيجة النهائية';
    protected static string $modelPluralLabel = 'النتائج النهائية';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['results', 'final_results'];

    protected $fillable = [
        'exam_session_id',
        'student_id',
        'course_offering_id',
        'coursework_score',
        'final_exam_score',
        'total_score',
        'grace_marks',
        'grade_label',
        'status',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'coursework_score' => 'decimal:2',
        'final_exam_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'grace_marks' => 'decimal:2',
        'status' => \App\Domains\Academic\Results\Enums\FinalResultStatus::class,
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // ==================== العلاقات ====================

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    // ==================== الحالات ====================

    public function isPassed(): bool
    {
        return $this->status === \App\Domains\Academic\Results\Enums\FinalResultStatus::Pass;
    }

    public function isFailed(): bool
    {
        return $this->status === \App\Domains\Academic\Results\Enums\FinalResultStatus::Fail;
    }

    public function isAbsent(): bool
    {
        return $this->status === \App\Domains\Academic\Results\Enums\FinalResultStatus::Absent;
    }

    // ==================== التقديرات ====================

    /**
     * تحديد التقدير بناءً على النسبة المئوية
     */
    public static function calculateGradeLabel(float $percentage): string
    {
        return app(GradingCalculatorService::class)->resolveGradeLabel($percentage);
    }
}
