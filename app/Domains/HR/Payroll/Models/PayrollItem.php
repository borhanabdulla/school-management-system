<?php

namespace App\Domains\HR\Payroll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\HR\Payroll\Enums\PayrollItemType;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_record_id',
        'name',
        'type',
        'category',
        'description',
        'amount',
        'is_manual_override',
        'original_amount',
        'source_type',
        'source_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // 1. Guards: منع التعديل إذا كان المسير مجمداً أو معتمداً
        static::saving(function (PayrollItem $item) {
            $item->ensureBatchEditable();
        });

        static::deleting(function (PayrollItem $item) {
            $item->ensureBatchEditable();
        });

        // 2. Source of Truth: إعادة حساب المجاميع تلقائياً عند أي تغيير
        static::saved(function (PayrollItem $item) {
            $item->record->recalculateTotals();
        });

        static::deleted(function (PayrollItem $item) {
            $item->record->recalculateTotals();
        });
    }

    protected $casts = [
        'type' => PayrollItemType::class,
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'is_manual_override' => 'boolean',
    ];

    // ==================== العلاقات ====================

    public function record(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class, 'payroll_record_id');
    }

    // ==================== Scopes ====================

    public function scopeEarnings($query)
    {
        return $query->where('type', PayrollItemType::Earning);
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', PayrollItemType::Deduction);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ==================== Accessors ====================

    public function getTypeLabelAttribute(): string
    {
        return $this->type?->label() ?? '';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'basic' => 'الراتب الأساسي',
            'housing' => 'بدل سكن',
            'transport' => 'بدل نقل',
            'lateness' => 'خصم تأخير',
            'absence' => 'خصم غياب',
            'tax' => 'ضريبة',
            'insurance' => 'تأمينات',
            'advance' => 'سلفة',
            'bonus' => 'مكافأة',
            default => $this->category,
        };
    }

    /**
     * القيمة مع الإشارة (+ للاستحقاق، - للاستقطاع)
     */
    public function getSignedAmountAttribute(): float
    {
        return $this->amount * ($this->type?->sign() ?? 1);
    }

    // ==================== Business Logic ====================

    /**
     * تعديل القيمة يدوياً (مع حفظ الأصلية)
     */
    public function override(float $newAmount): void
    {
        if (!$this->is_manual_override) {
            $this->original_amount = $this->amount;
        }

        $this->amount = $newAmount;
        $this->is_manual_override = true;
        $this->save();

        // إعادة حساب الإجماليات
        $this->record->recalculateTotals();
    }
    /**
     * التحقق من إمكانية تعديل البند بناءً على حالة المسير
     */
    public function ensureBatchEditable(): void
    {
        // إذا لم يكن مرتبطاً بسجل بعد (نظرياً لا يحدث في saving إلا إذا كان جديداً جداً)
        if (!$this->payroll_record_id)
            return;

        // تحميل العلاقة إذا لم تكن محملة
        $record = $this->relationLoaded('record')
            ? $this->record
            : PayrollRecord::find($this->payroll_record_id);

        if ($record) {
            $batch = $record->relationLoaded('batch')
                ? $record->batch
                : PayrollBatch::find($record->payroll_batch_id);

            if ($batch) {
                $batch->ensureEditable();
            }
        }
    }
}
