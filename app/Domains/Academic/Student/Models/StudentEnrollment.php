<?php

namespace App\Domains\Academic\Student\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Enums\EnrollmentType;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;

class StudentEnrollment extends Model
{
    use HasFactory;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\HasAcademicScope;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'تسجيل طالب';
    protected static string $modelPluralLabel = 'تسجيلات الطلاب';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];

    protected $table = 'student_enrollments';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'grade_id',
        'class_section_id',
        'enrollment_date',
        'drop_date',
        'enrollment_type',
        'status',
    ];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'enrollment_type' => EnrollmentType::class,
        'enrollment_date' => 'date',
        'drop_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade() //عمليه اربط بين الطلاب والمستويات  بحيث نستطيع معرفه المستوى الذي ينتمي اليه الطالب    
    {
        return $this->belongsTo(Grade::class);
    }

    public function classSection() //عمليه اربط بين الطلاب والصفات بحيث نستطيع معرفه الصف الذي ينتمي اليه الطالب
    {
        return $this->belongsTo(ClassSection::class);
    }
}
