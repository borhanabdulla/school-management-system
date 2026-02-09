<?php

namespace App\Domains\Academic\Student\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;

class StudentMark extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'درجة الطالب';
    protected static string $modelPluralLabel = 'درجات الطلاب';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['student_marks'];
    protected $fillable = [
        'student_id',
        'course_offering_id',
        'academic_year_id',
        'term_id',
        'assessment_id',
        'template_category_id',
        'raw_score',
        'scaled_score',
        'is_missing',
        'is_excused',
        'feedback',
        'graded_by_user_id',
        'amended_by',
        'amended_at',
        'amendment_reason',
    ];

    protected $casts = [
        'raw_score' => 'decimal:2',
        'scaled_score' => 'decimal:2',
        'is_missing' => 'boolean',
        'is_excused' => 'boolean',
        'amended_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_user_id');
    }
}
