<?php

namespace App\Domains\Academic\Subject\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Subject\Models\Subject;

class GradeSubject extends Pivot
{
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'مادة صف';
    protected static string $modelPluralLabel = 'مواد الصفوف';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];

    protected $table = 'grade_subjects';
    public $incrementing = true;

    /**
     * علاقة المادة
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * علاقة الصف الدراسي
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}