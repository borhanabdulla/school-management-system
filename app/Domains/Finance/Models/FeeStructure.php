<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Domains\Academic\Grade\Models\Grade;

class FeeStructure extends Model
{
    use HandlesSafeDelete, HasModelLabels;

    protected $fillable = [
        'academic_year_id',
        'fee_type_id',
        'grade_id',
        'amount',
        'due_date'
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    protected static function booted()
    {
        static::creating(function ($feeStructure) {
            app(\App\Domains\Finance\Services\FinancialLockService::class)->ensureOpen($feeStructure->academic_year_id);
        });

        static::updating(function ($feeStructure) {
            // If academic_year_id is changed, check both (though usually it doesn't change)
            if ($feeStructure->isDirty('academic_year_id')) {
                app(\App\Domains\Finance\Services\FinancialLockService::class)->ensureOpen($feeStructure->getOriginal('academic_year_id'));
                app(\App\Domains\Finance\Services\FinancialLockService::class)->ensureOpen($feeStructure->academic_year_id);
            } else {
                app(\App\Domains\Finance\Services\FinancialLockService::class)->ensureOpen($feeStructure->academic_year_id);
            }
        });

        static::deleting(function ($feeStructure) {
            app(\App\Domains\Finance\Services\FinancialLockService::class)->ensureOpen($feeStructure->academic_year_id);
        });
    }
}
