<?php

namespace App\Domains\Academic\Results\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Term\Models\Term;

class TermResult extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels;

    protected static string $modelLabel = 'نتيجة ترم';
    protected static string $modelPluralLabel = 'نتائج الترم';

    protected $fillable = [
        'student_id',
        'course_offering_id',
        'term_id',
        'coursework_score',
        'exam_score',
        'total_score',
        'max_score',
        'percentage',
        'grade_letter',
        'is_passed',
        'calculated_at',
    ];

    protected $casts = [
        'coursework_score' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function failures(): HasMany
    {
        return $this->hasMany(TermResultFailure::class);
    }
}
