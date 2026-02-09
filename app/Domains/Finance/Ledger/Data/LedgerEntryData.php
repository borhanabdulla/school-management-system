<?php

namespace App\Domains\Finance\Ledger\Data;

use App\Domains\Finance\Ledger\Enums\LedgerCategory;
use App\Domains\Finance\Ledger\Enums\LedgerDirection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * LedgerEntryData - بيانات إنشاء قيد في السجل المالي
 * 
 * Data Flow:
 * Event (PayrollBatchPaid / PaymentReceived)
 *   → Listener (يبني هذا الـ DTO)
 *   → LedgerService::record() (يستخدم الـ DTO)
 *   → LedgerEntry Model (يُحفظ في DB)
 */
readonly class LedgerEntryData
{
    public function __construct(
        public Carbon $entryDate,
        public LedgerDirection $direction,
        public float $amount,
        public LedgerCategory $category,
        public string $externalKey,
        public string $sourceType,
        public int $sourceId,
        public int $createdBy,
        public ?int $academicYearId = null,
        public string $currency = 'SAR',
        public ?string $notes = null,
    ) {
    }

    /**
     * إنشاء قيد دخل من دفعة طالب
     */
    public static function forStudentPayment(
        Model $payment,
        int $createdBy,
        ?string $notes = null
    ): self {
        return new self(
            entryDate: $payment->paid_at ?? $payment->created_at,
            direction: LedgerDirection::In,
            amount: (float) $payment->amount,
            category: LedgerCategory::StudentPayment,
            externalKey: "payment:{$payment->id}",
            sourceType: get_class($payment),
            sourceId: $payment->id,
            createdBy: $createdBy,
            academicYearId: $payment->invoice?->academic_year_id,
            notes: $notes,
        );
    }

    /**
     * إنشاء قيد خرج من صرف رواتب
     */
    public static function forPayrollPayout(
        Model $batch,
        int $createdBy,
        ?string $notes = null
    ): self {
        return new self(
            entryDate: $batch->paid_at,
            direction: LedgerDirection::Out,
            amount: (float) $batch->total_net,
            category: LedgerCategory::PayrollPayout,
            externalKey: "payroll_batch:{$batch->id}",
            sourceType: get_class($batch),
            sourceId: $batch->id,
            createdBy: $createdBy,
            academicYearId: $batch->academic_year_id, // PR-C1
            notes: $notes ?? "صرف مسير {$batch->name}",
        );
    }

    /**
     * إنشاء قيد خرج من مصروف عام
     */
    public static function forExpense(
        Model $expense,
        int $createdBy,
        ?string $notes = null
    ): self {
        return new self(
            entryDate: $expense->expense_date ?? $expense->created_at,
            direction: LedgerDirection::Out,
            amount: (float) $expense->amount,
            category: LedgerCategory::Expense,
            externalKey: "expense:{$expense->id}",
            sourceType: get_class($expense),
            sourceId: $expense->id,
            createdBy: $createdBy,
            academicYearId: $expense->academic_year_id ?? null,
            notes: $notes,
        );
    }

    /**
     * تحويل إلى مصفوفة للحفظ في قاعدة البيانات
     */
    public function toArray(): array
    {
        return [
            'entry_date' => $this->entryDate,
            'direction' => $this->direction,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'category' => $this->category,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'created_by' => $this->createdBy,
            'academic_year_id' => $this->academicYearId,
            'external_key' => $this->externalKey,
            'notes' => $this->notes,
        ];
    }
}
