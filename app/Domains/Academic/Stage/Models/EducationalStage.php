<?php

namespace App\Domains\Academic\Stage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Grade\Models\Grade;

class EducationalStage extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\EducationalStageFactory::new();
    }

    protected $fillable = ['name', 'rank', 'min_passing_percentage', 'grading_system'];

    public function grades(): HasMany
    {
        // دائماً نرتب الصفوف حسب التسلسل لضمان العرض الصحيح
        return $this->hasMany(Grade::class)->orderBy('level_order');
    }

    // جلب الصفوف مع شعبها لسنة معينة
    public function gradesWithSections($yearId)
    {
        return $this->grades()
            ->with([
                'sections' => function ($query) use ($yearId) {
                    $query->where('academic_year_id', $yearId)
                        ->orderBy('name');
                }
            ])
            ->get();
    }
}