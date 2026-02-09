<?php

namespace App\Domains\HR\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Subject\Models\Subject;

class Department extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = ['name'];

    /**
     * الموظفون في هذا القسم
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * المواد التابعة للقسم
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * الوظائف في هذا القسم
     */
    public function jobPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }
}
