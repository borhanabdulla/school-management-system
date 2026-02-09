<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Term\Models\Term;

class GradingTemplate extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'قالب التقييم';
    protected static string $modelPluralLabel = 'قوالب التقييم';
    protected static string $labelAttribute = 'name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'subjectConfigs' => 'إعدادات المواد',
        'categories' => 'فئات التقييم',
    ];

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['grading', 'templates'];

    protected $fillable = [
        'name',
        'total_max_score',
        'pass_score',
        'rounding_rule',
        'rounding_precision',
        'academic_year_id',
        'grade_id',
        'term_id',
    ];

    protected $casts = [
        'total_max_score' => 'decimal:2',
        'pass_score' => 'decimal:2',
        'rounding_precision' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TemplateCategory::class)->orderBy('order');
    }

    public function subjectConfigs(): HasMany
    {
        return $this->hasMany(SubjectGradingConfig::class);
    }
}
