<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Homework\Models\Homework;

/**
 * Assessment - نموذج التقييم
 * 
 * تم نقله من Assessment subdomain ودمجه مع Grading
 */
class Assessment extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'التقييم';
    protected static string $modelPluralLabel = 'التقييمات';
    protected static string $labelAttribute = 'title';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['assessments'];

    protected $fillable = [
        'course_offering_id',
        'template_category_id',
        'title',
        'max_score',
        'weight',
        'due_date',
        'is_published',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'due_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }

    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class);
    }
}
