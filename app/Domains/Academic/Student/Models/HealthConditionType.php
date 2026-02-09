<?php

namespace App\Domains\Academic\Student\Models;

use Illuminate\Database\Eloquent\Model;

class HealthConditionType extends Model
{
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'نوع حالة صحية';
    protected static string $modelPluralLabel = 'أنواع الحالات الصحية';
    protected static string $labelAttribute = 'name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'conditions' => 'حالات صحية مرتبطة',
    ];

    protected $fillable = ['name', 'description', 'severity_level', 'action_plan'];

    /**
     * الحالات الصحية من هذا النوع
     */
    public function conditions()
    {
        return $this->hasMany(StudentHealthCondition::class, 'health_condition_type_id');
    }
}
