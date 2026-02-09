<?php

namespace App\Domains\Finance\Ledger\Models;

use App\Domains\Finance\Ledger\Enums\LedgerCategory;
use App\Domains\Finance\Ledger\Enums\LedgerDirection;
use App\Domains\Finance\Ledger\Enums\LedgerStatus;
use App\Domains\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'entry_date',
        'direction',
        'amount',
        'currency',
        'category',
        'source_type',
        'source_id',
        'status',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'notes',
        'external_key',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
        'amount' => 'decimal:2',
        'direction' => LedgerDirection::class,
        'category' => LedgerCategory::class,
        'status' => LedgerStatus::class,
        'cancelled_at' => 'datetime',
    ];

    // ==================== العلاقات ====================

    /**
     * السنة الأكاديمية (PR-D1)
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Academic\AcademicYear\Models\AcademicYear::class);
    }

    /**
     * المصدر (Payment, PayrollBatch, Expense)
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * من أنشأ القيد
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * من ألغى القيد
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ==================== Scopes ====================

    public function scopePosted($query)
    {
        return $query->where('status', LedgerStatus::Posted);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', LedgerStatus::Cancelled);
    }

    public function scopeIncome($query)
    {
        return $query->where('direction', LedgerDirection::In);
    }

    public function scopeExpense($query)
    {
        return $query->where('direction', LedgerDirection::Out);
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('entry_date', [$start, $end]);
    }

    public function scopeByCategory($query, LedgerCategory $category)
    {
        return $query->where('category', $category);
    }

    // ==================== Helpers ====================

    /**
     * هل القيد ملغى؟
     */
    public function isCancelled(): bool
    {
        return $this->status === LedgerStatus::Cancelled;
    }

    /**
     * هل القيد دخل؟
     */
    public function isIncome(): bool
    {
        return $this->direction === LedgerDirection::In;
    }
}
