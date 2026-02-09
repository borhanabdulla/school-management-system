<?php

namespace App\Domains\Academic\Subject\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\GradeSubject;

class Subject extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\SubjectFactory::new();
    }
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\InvalidatesCache;

    /**
     * اسم الموديل بالعربي
     */
    protected static string $modelLabel = 'مادة دراسية';
    protected static string $modelPluralLabel = 'مواد دراسية';

    /**
     * Cache Tags
     */
    protected array $cacheTags = ['academic', 'subjects'];

    protected $fillable = ['name', 'code', 'type', 'description'];

    public function grades()
    {
        return $this->belongsToMany(Grade::class, 'grade_subjects')
            ->withPivot(['id', 'credit_hours', 'term_type', 'is_active'])
            ->using(GradeSubject::class)->withTimestamps();
    }
    // public function gradess()
    // {
    //     return $this->belongstomany(grade::class, 'grade_subjects')->withpiviot(['id','credit_hours','max_grade','pass_grade','term_type','is_active'])->using(GradeSubject::class)->withTimestamps();
    // }
}



