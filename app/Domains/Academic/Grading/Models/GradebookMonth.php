<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use App\Infrastructure\Traits\HasAcademicScope;
use Database\Factories\Domains\Academic\Grading\Models\GradebookMonthFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Term\Models\Term;

class GradebookMonth extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache, HasAcademicScope;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): GradebookMonthFactory
    {
        return GradebookMonthFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'شهر دفتر الدرجات';
    protected static string $modelPluralLabel = 'أشهر دفتر الدرجات';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['grading', 'gradebook_months'];

    protected $fillable = [
        'term_id',
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'order' => 'integer',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(MonthlyGrade::class, 'gradebook_month_id');
    }

    /**
     * @deprecated Use GradebookMonthService::generateForTerm instead.
     */
    public static function generateForTerm(Term $term): void
    {
        app(\App\Domains\Academic\Grading\Services\GradebookMonthService::class)->generateForTerm($term);
    }
}
