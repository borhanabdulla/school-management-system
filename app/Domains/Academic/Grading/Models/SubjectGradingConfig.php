<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Grade\Models\Grade;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;

class SubjectGradingConfig extends Model
{
    use HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'إعداد تقييم المادة';
    protected static string $modelPluralLabel = 'إعدادات تقييم المواد';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['grading', 'subject_configs'];

    protected $fillable = [
        'subject_id',
        'grade_id',
        'term_id',
        'grading_template_id',
        'max_score',
        'pass_score',
        'is_continuous',
        'counts_in_gpa',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'pass_score' => 'decimal:2',
        'is_continuous' => 'boolean',
        'counts_in_gpa' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(GradingTemplate::class, 'grading_template_id');
    }
}
