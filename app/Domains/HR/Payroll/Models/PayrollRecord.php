<?php

namespace App\Domains\HR\Payroll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\Shared\Models\User;
use App\Domains\HR\Payroll\Enums\PayrollItemType;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_batch_id',
        'staff_id',
        'contract_id',
        'basic_salary',
        'working_days',
        'days_worked',
        'days_absent',
        'days_late',
        'gross_earnings',
        'total_deductions',
        'net_payable',
        'manual_adjustment',
        'adjustment_reason',
        'adjusted_by',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'gross_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'manual_adjustment' => 'decimal:2',
    ];

    // ==================== العلاقات ====================

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PayrollBatch::class, 'payroll_batch_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function adjustedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    // ==================== Accessors ====================

    /**
     * الاستحقاقات (البنود الموجبة)
     */
    public function getEarningsAttribute()
    {
        return $this->items()->where('type', PayrollItemType::Earning)->get();
    }

    /**
     * الاستقطاعات (البنود السالبة)
     */
    public function getDeductionsAttribute()
    {
        return $this->items()->where('type', PayrollItemType::Deduction)->get();
    }

    /**
     * اسم الموظف للعرض
     */
    public function getEmployeeNameAttribute(): string
    {
        return $this->staff?->full_name ?? 'غير محدد';
    }

    // ==================== Business Logic ====================

    /**
     * إعادة حساب الإجماليات من البنود
     */
    /**
     * إعادة حساب الإجماليات من البنود (Source of Truth)
     */
    public function recalculateTotals(): void
    {
        app(\App\Domains\HR\Payroll\Services\PayrollTotalsService::class)->recalculateRecord($this);
    }
}
