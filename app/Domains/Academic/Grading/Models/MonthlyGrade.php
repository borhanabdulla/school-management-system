<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use App\Domains\Academic\Grading\Events\MonthlyGradeSaved;
use Database\Factories\Domains\Academic\Grading\Models\MonthlyGradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\Grading\Models\TemplateCategory;

class MonthlyGrade extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    protected static function booted(): void
    {
        static::saved(function (self $grade): void {
            event(new MonthlyGradeSaved($grade));
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): MonthlyGradeFactory
    {
        return MonthlyGradeFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'درجة شهرية';
    protected static string $modelPluralLabel = 'درجات شهرية';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['grading', 'monthly_grades'];

    protected $fillable = [
        'student_id',
        'course_offering_id',
        'gradebook_month_id',
        'category_key',
        'template_category_id',
        'category',
        'score',
        'max_score',
        'notes',
        'graded_by',
        'amended_by',
        'amended_at',
        'amendment_reason',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'amended_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(GradebookMonth::class, 'gradebook_month_id');
    }

    public function gradebookMonth(): BelongsTo
    {
        return $this->belongsTo(GradebookMonth::class, 'gradebook_month_id');
    }

    public function templateCategory(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * حساب درجة الحضور تلقائياً
     */
    /**
     * حساب درجة الحضور تلقائياً
     * 
     * @deprecated Use GradingCalculatorService::calculateAttendanceScore instead
     */
    public static function calculateAttendanceScore(
        int $studentId,
        int $courseOfferingId,
        int $monthId,
        int $absenceCount,
        GradebookSettings $settings
    ): float {
        return app(\App\Domains\Academic\Grading\Services\GradingCalculatorService::class)
            ->calculateAttendanceScore($absenceCount, $settings);
    }
}
