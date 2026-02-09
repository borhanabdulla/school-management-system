<?php

namespace App\Domains\Academic\Grade\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Subject\Models\GradeSubject;

class Grade extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\GradeFactory::new();
    }
    use \App\Infrastructure\Traits\Scopes\GradeScopes;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\InvalidatesCache;

    /**
     * اسم الموديل بالعربي
     */
    protected static string $modelLabel = 'صف دراسي';
    protected static string $modelPluralLabel = 'صفوف دراسية';

    /**
     * العلاقات المحمية من الحذف
     */
    protected array $protectedRelations = [
        'sections' => 'شعب دراسية',
        'subjects' => 'مواد دراسية',
    ];

    /**
     * Cache Tags
     */
    protected array $cacheTags = ['academic', 'grades'];

    protected $fillable = ['educational_stage_id', 'name', 'level_order', 'next_grade_id', 'min_age', 'max_age'];
    // علاقة بين الصف الدراسي والمرحلة الدراسية
    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class, 'educational_stage_id');
    }
    //  علاقه بين الصف  الحالي والذي يليه
    public function nextGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'next_grade_id');
    }
    // علاقة بين الصف الدراسي والشعبة
    public function sections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    /**
     * Get all students enrolled in this grade (via student_enrollments).
     * This includes students who are NOT assigned to a section yet.
     */
    public function studentsEnrolled(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domains\Academic\Student\Models\Student::class,
            'student_enrollments',
            'grade_id',
            'student_id'
        )
            ->withPivot(['academic_year_id', 'class_section_id', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all students in this grade through sections.
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Domains\Academic\Student\Models\Student::class,
            ClassSection::class,
            'grade_id', // Foreign key on class_sections table...
            'current_class_section_id', // Foreign key on students table...
            'id', // Local key on grades table...
            'id' // Local key on class_sections table...
        );
    }



    // نستيطع من خلال هذا العلاقة جلب الشب  لسنة معينة  بدون ان نحتاج   الى استخدام العلاقة السابقة 
    public function sectionsForYear($yearId)
    {
        return $this->hasMany(ClassSection::class)->where('academic_year_id', $yearId);
    }

    // عدد الطلاب في هذا الصف لسنة معينة
    public function getStudentCountForYear($yearId)
    {
        return $this->sectionsForYear($yearId)->withCount('students')->get()->sum('students_count');
    }

    // عدد المواد في هذا الصف لترم معين
    // public function getSubjectCountForTerm($termId)
    // {
    //     // TODO: سنربطها لاحقاً بجدول curriculum_plans عندما يكون جاهزاً
    //     // حالياً نرجع قيمة افتراضية بناءً على المرحلة
    //     $stage = $this->stage;

    //     // قيم افتراضية حسب المرحلة
    //     if ($stage) {
    //         if ($stage->rank == 1) return 8;  // ابتدائي: 8 مواد
    //         if ($stage->rank == 2) return 10; // متوسط: 10 مواد
    //         if ($stage->rank == 3) return 12; // ثانوي: 12 مادة
    //     }

    //     return 8; // افتراضي
    // }

    //     public function subjects()
// {
//     return $this->belongsToMany(Subject::class, 'grade_subjects')
//                 ->withPivot(['id', 'credit_hours', 'max_grade', 'pass_grade', 'duration_type', 'is_active'])
//                 ->using(GradeSubject::class);
// }

    public function subjects()  // هذه تعمل على العلاقة بين الصف الدراسي والمواد    
    {
        return $this->belongsToMany(Subject::class, 'grade_subjects')
            ->withPivot(['id', 'credit_hours', 'term_type', 'is_active'])// الشرط  يعني هل المادة نشطة   
            ->using(GradeSubject::class)->withTimestamps();
    }
}
