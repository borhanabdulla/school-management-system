<?php

namespace App\Domains\Academic\Student\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionApplication extends Model
{
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'طلب قبول';
    protected static string $modelPluralLabel = 'طلبات القبول';
    protected static string $labelAttribute = 'reference_no';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'student' => 'طالب مسجل',
    ];
    protected $fillable = [
        'reference_no',
        'academic_year_id',
        'target_grade_id',
        'student_national_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'status',
        'interview_notes',
        'entrance_exam_score'
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
