<?php

namespace App\Domains\Academic\Student\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPreviousHistory extends Model
{
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'سجل سابق للطالب';
    protected static string $modelPluralLabel = 'السجلات السابقة للطلاب';
    protected static string $labelAttribute = 'school_name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];

    protected $fillable = [
        'student_id',
        'school_name',
        'previous_curriculum',
        'last_grade_completed',
        'completion_year',
        'last_gpa',
        'reason_for_transfer',
        'conduct_summary'
    ];

    /**
     * الطالب صاحب السجل
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
