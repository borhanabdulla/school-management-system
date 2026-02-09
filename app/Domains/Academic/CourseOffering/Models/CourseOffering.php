<?php

namespace App\Domains\Academic\CourseOffering\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;

class CourseOffering extends Model
{
    use HasFactory;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\InvalidatesCache;
    use \App\Infrastructure\Traits\HasAcademicScope;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'عرض مادة';
    protected static string $modelPluralLabel = 'عروض المواد';
    protected static string $labelAttribute = 'id';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'timetables' => 'جداول الحصص',
        'homeworks' => 'الواجبات',
    ];

    // ============================================
    // Cache Tags
    // ============================================
    protected array $cacheTags = ['course_offerings', 'academic'];

    protected static function newFactory()
    {
        return \Database\Factories\Domains\Academic\CourseOffering\Models\CourseOfferingFactory::new();
    }

    protected $fillable = [
        'academic_year_id',
        'term_id',
        'subject_id',
        'class_section_id',
        'teacher_id',
    ];

    // علاقة بالسنة الدراسية
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // علاقة بالفصل الدراسي (اختياري)
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    // علاقة بالمادة الدراسية
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // علاقة بالشعبة
    public function classSection()
    {
        return $this->belongsTo(ClassSection::class);
    }

    // علاقة بالمعلم
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // علاقة بالجداول الدراسية
    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * الصف المرتبط (عبر الشعبة)
     */
    public function getGradeAttribute()
    {
        return $this->classSection?->grade;
    }

    // طلاب هذه المادة (عبر الشعبة)
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'class_section_id', // Foreign key on StudentEnrollment
            'id', // Foreign key on Student
            'class_section_id', // Local key on CourseOffering
            'student_id' // Local key on StudentEnrollment
        )->where('student_enrollments.status', 'active');
    }

    // علاقة بالواجبات
    public function homeworks()
    {
        return $this->hasMany(\App\Domains\Academic\Homework\Models\Homework::class);
    }
}
