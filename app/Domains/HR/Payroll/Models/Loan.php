<?php

namespace App\Domains\HR\Payroll\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;

class Loan extends Model
{
    use SoftDeletes, HandlesSafeDelete, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'القرض';
    protected static string $modelPluralLabel = 'القروض';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'installments' => 'الأقساط',
    ];

    protected $fillable = [
        'staff_id',
        'amount',
        'paid_amount',
        'installments_count',
        'monthly_installment',
        'reason',
        'status',
        'start_date',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'status' => \App\Domains\HR\Payroll\Enums\LoanStatus::class,
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class);
    }

    public function getProgressAttribute(): float
    {
        if ($this->amount <= 0)
            return 0;
        return min(100, ($this->paid_amount / $this->amount) * 100);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === \App\Domains\HR\Payroll\Enums\LoanStatus::Approved && $this->paid_amount < $this->amount;
    }
}
