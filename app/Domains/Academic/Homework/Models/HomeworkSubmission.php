<?php

namespace App\Domains\Academic\Homework\Models;

use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Student\Models\Student;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'تسليم الواجب';
    protected static string $modelPluralLabel = 'تسليمات الواجبات';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['homework', 'submissions'];

    protected $fillable = [
        'homework_id',
        'student_id',
        'status',
        'submitted_at',
        'file_path',
        'score',
        'feedback',
    ];

    protected $casts = [
        'status' => SubmissionStatus::class,
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
