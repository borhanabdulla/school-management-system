<?php

namespace App\Domains\Academic\Control\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlMark extends Model
{
    use HasFactory;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'درجة كنترول';
    protected static string $modelPluralLabel = 'درجات الكنترول';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];
    protected $fillable = [
        'exam_seating_id',
        'course_offering_id',
        'score',
        'is_absent',
        'entered_by',
        'audited_by',
        'audited_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_absent' => 'boolean',
        'audited_at' => 'datetime',
    ];

    // ==================== العلاقات ====================

    public function seating(): BelongsTo
    {
        return $this->belongsTo(ExamSeating::class, 'exam_seating_id');
    }

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function enteredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function auditedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    // ==================== الوصول غير المباشر ====================

    /**
     * الحصول على الطالب (عبر سجل الجلوس)
     * ⚠️ استخدم بحذر - هذا يكسر مبدأ "الرصد الأعمى"
     */
    public function getStudentAttribute(): ?Student
    {
        return $this->seating?->student;
    }

    /**
     * الحصول على الدورة الامتحانية
     */
    public function getSessionAttribute(): ?ExamSession
    {
        return $this->seating?->session;
    }
}
