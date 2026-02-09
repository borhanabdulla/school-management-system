<?php

namespace App\Domains\Academic\Homework\Models;

use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\Assessment;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Homework extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels, InvalidatesCache;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'الواجب المنزلي';
    protected static string $modelPluralLabel = 'الواجبات المنزلية';
    protected static string $labelAttribute = 'title';

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['homework'];

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'submissions' => 'تسليمات الطلاب',
    ];

    protected $table = 'homeworks';

    protected $fillable = [
        'course_offering_id',
        'assessment_id',
        'title',
        'description',
        'attachment_path',
        'submission_type',
        'status',
        'due_date',
        'allow_late',
        'max_score',
    ];

    protected $casts = [
        'submission_type' => SubmissionType::class,
        'status' => HomeworkStatus::class,
        'due_date' => 'datetime',
        'allow_late' => 'boolean',
        'max_score' => 'decimal:2',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }
}
