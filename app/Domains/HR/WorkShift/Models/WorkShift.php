<?php

namespace App\Domains\HR\WorkShift\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WorkShift extends Model
{
    protected $fillable = [
        'name',
        'season',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'working_days',
        'works_on_holidays',
        'is_active',
    ];

    protected $casts = [
        'working_days' => 'array',
        'works_on_holidays' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * الموظفون المرتبطون بهذه الوردية
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * الوقت المجدول بشكل مقروء
     */
    public function getScheduleDisplayAttribute(): string
    {
        $start = Carbon::parse($this->start_time)->format('h:i A');
        $end = Carbon::parse($this->end_time)->format('h:i A');
        return "{$start} - {$end}";
    }

    /**
     * Scope: الورديات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: ورديات الموسم الحالي
     */
    public function scopeCurrentSeason($query)
    {
        $currentMonth = Carbon::now()->month;
        // الصيف: يونيو - سبتمبر (6-9)
        $isSummer = $currentMonth >= 6 && $currentMonth <= 9;
        $season = $isSummer ? 'summer' : 'winter';

        return $query->where(function ($q) use ($season) {
            $q->where('season', $season)
                ->orWhere('season', 'all');
        });
    }

    /**
     * منع الحذف إذا كانت مرتبطة بموظفين
     */
    public function canBeDeleted(): bool
    {
        return $this->staff()->count() === 0;
    }
}
