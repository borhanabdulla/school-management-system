<?php

namespace App\Domains\Academic\ClassSection\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Student\Models\StudentEnrollment;

class ClassSection extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\ClassSectionFactory::new();
    }
    use \App\Infrastructure\Traits\HasAcademicScope;
    use \App\Domains\Academic\ClassSection\Traits\ClassSectionScopes {
        \App\Infrastructure\Traits\HasAcademicScope::scopeForYear insteadof \App\Domains\Academic\ClassSection\Traits\ClassSectionScopes;
    }
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\InvalidatesCache;

    /**
     * اسم الموديل بالعربي
     */
    protected static string $modelLabel = 'شعبة دراسية';
    protected static string $modelPluralLabel = 'شعب دراسية';

    /**
     * العلاقات المحمية من الحذف
     */
    protected array $protectedRelations = [
        'students' => 'طلاب',
        'enrollments' => 'تسجيلات طلاب',
        'timetables' => 'جداول الحصص',
        'attendances' => 'سجلات الحضور',
        'courseOfferings' => 'عروض المواد',
        'promotions' => 'قرارات الترحيل',
    ];

    /**
     * Cache Tags
     */
    protected array $cacheTags = ['academic', 'sections'];

    /**
     * تفعيل التعبئة التلقائية للسنة الدراسية
     */
    protected bool $autoFillAcademicYear = true;

    protected $fillable = [
        'name',
        'grade_id',
        'academic_year_id',
        'max_capacity',
        'gender_type',
        'is_active'
    ];

    protected $casts = [
        'gender_type' => \App\Domains\Academic\ClassSection\Enums\SectionGenderType::class,
    ];
    // ربط ال فصل الدراسي بالصف الدراسي 
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
    // ربط ال فصل الدراسي بالسنة الدراسية  
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }



    // علاقة الطلاب
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'current_class_section_id');
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /**
     * الاسم الكامل للشعبة (الصف - الشعبة)
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->grade->name} - {$this->name}";
    }

    public function timetables()
    {
        return $this->hasMany(\App\Domains\Academic\Timetable\Models\Timetable::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'class_section_id');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class, 'to_class_section_id');
    }
}
