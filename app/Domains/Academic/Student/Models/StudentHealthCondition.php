<?php

namespace App\Domains\Academic\Student\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHealthCondition extends Model
{
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'حالة صحية للطالب';
    protected static string $modelPluralLabel = 'الحالات الصحية للطلاب';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];

    protected $fillable = ['student_id', 'health_condition_type_id', 'notes'];

    /**
     * الطالب صاحب الحالة
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * نوع الحالة الصحية
     */
    public function type()
    {
        return $this->belongsTo(HealthConditionType::class, 'health_condition_type_id');
    }
}
