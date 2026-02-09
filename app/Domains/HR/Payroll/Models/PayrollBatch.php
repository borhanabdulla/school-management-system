<?php

namespace App\Domains\HR\Payroll\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\HR\Payroll\Exceptions\PayrollFreezeException;
use App\Domains\HR\Payroll\Exceptions\InvalidWorkflowStateException;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\Shared\Models\User;

class PayrollBatch extends Model
{
    use HasFactory, HasModelLabels;

    protected static function newFactory()
    {
        return \Database\Factories\PayrollBatchFactory::new();
    }

    /**
     * حماية الموديل من التعديلات غير القانونية
     */
    protected static function booted(): void
    {
        static::updating(function (PayrollBatch $batch) {
            // منع تعديل تفاصيل الدفع بعد الصرف
            if ($batch->getOriginal('status') === PayrollBatchStatus::Paid->value) {
                $protectedFields = ['payout_method', 'payout_reference', 'paid_by', 'paid_at'];
                foreach ($protectedFields as $field) {
                    if ($batch->isDirty($field)) {
                        throw new PayrollFreezeException('لا يمكن تعديل بيانات الصرف بعد الدفع');
                    }
                }
            }
        });

        // منع الحذف بعد التجميد
        static::deleting(function (PayrollBatch $batch) {
            if ($batch->status !== PayrollBatchStatus::Draft) {
                throw new PayrollFreezeException('لا يمكن حذف مسير بعد ما يكون مجمد');
            }
        });
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'دفعة رواتب';
    protected static string $modelPluralLabel = 'دفعات الرواتب';
    protected static string $labelAttribute = 'name';

    protected $fillable = [
        'name',
        'academic_year_id',
        'period_start',
        'period_end',
        'year',
        'month',
        'status',
        'frozen_at',
        'approved_at',
        'paid_at',
        'generated_by',
        'frozen_by',
        'approved_by',
        'total_gross',
        'total_deductions',
        'total_net',
        'employees_count',
        'notes',
        'paid_by',
        'payout_method',
        'payout_reference',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'frozen_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'status' => \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::class,
        'payout_method' => \App\Domains\HR\Payroll\Enums\PayoutMethod::class,
    ];

    // ==================== العلاقات ====================

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Academic\AcademicYear\Models\AcademicYear::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function frozenByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'frozen_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ==================== Scopes ====================

    public function scopeDraft($query)
    {
        return $query->where('status', \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Draft);
    }

    public function scopeFrozen($query)
    {
        return $query->where('status', \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Frozen);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Approved);
    }

    public function scopePaid($query)
    {
        return $query->where('status', \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Paid);
    }

    // ==================== Accessors ====================



    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->translatedFormat('F Y');
    }

    // ==================== State Machine ====================

    public function canFreeze(): bool
    {
        return $this->status === \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Draft;
    }

    public function canApprove(): bool
    {
        return $this->status === \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Frozen;
    }

    public function canPay(): bool
    {
        return $this->status === \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Approved;
    }

    /**
     * تجميد المسير
     */
    public function freeze(int $userId): void
    {
        if (!$this->canFreeze()) {
            throw new InvalidWorkflowStateException('لا يمكن قفل دفعة بحالة: ' . $this->status_label);
        }

        $this->update([
            'status' => \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Frozen,
            'frozen_at' => now(),
            'frozen_by' => $userId,
        ]);
    }

    /**
     * اعتماد المسير
     */
    public function approve(int $userId): void
    {
        if (!$this->canApprove()) {
            throw new InvalidWorkflowStateException('لا يمكن اعتماد دفعة بحالة: ' . $this->status_label);
        }

        $this->update([
            'status' => \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $userId,
        ]);
    }

    /**
     * صرف المسير
     */
    /**
     * صرف المسير
     */
    public function markAsPaid(?int $paidBy = null, ?\App\Domains\HR\Payroll\Enums\PayoutMethod $method = null, ?string $reference = null): void
    {
        if (!$this->canPay()) {
            throw new InvalidWorkflowStateException('لا يمكن صرف دفعة بحالة: ' . $this->status_label);
        }

        $this->update([
            'status' => \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Paid,
            'paid_at' => now(),
            'paid_by' => $paidBy,
            'payout_method' => $method,
            'payout_reference' => $reference,
        ]);
    }

    /**
     * التحقق من إمكانية التعديل
     */
    public function ensureEditable(): void
    {
        if ($this->status !== \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Draft) {
            throw new PayrollFreezeException();
        }
    }
    /**
     * إعادة حساب الإجماليات من السجلات (Source of Truth)
     */
    public function recalculateTotals(): void
    {
        app(\App\Domains\HR\Payroll\Services\PayrollTotalsService::class)->recalculateBatch($this);
    }
}
