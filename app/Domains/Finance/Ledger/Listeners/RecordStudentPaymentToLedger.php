<?php

namespace App\Domains\Finance\Ledger\Listeners;

use App\Domains\Finance\Ledger\Data\LedgerEntryData;
use App\Domains\Finance\Ledger\Services\LedgerService;
use App\Domains\Finance\Events\PaymentReceived;

/**
 * تسجيل دفعات الطلاب في السجل المالي
 * 
 * PR-CF1: Queue Context + Source of Truth Fix
 * - في HTTP Context: created_by يحفظ من قام بالعملية فعلاً
 * - في Queue Context: auth()->id() = null دائماً
 * - created_by هو الـ **Source of Truth** لمن أنشأ الدفعة
 */
class RecordStudentPaymentToLedger
{
    public function __construct(protected LedgerService $ledgerService)
    {
    }

    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;

        // ✅ PR-CF1: استخدام created_by كـ Source of Truth
        // received_by لا يوجد في الجدول - كان خطأ
        // في Queue Context: auth()->id() = null
        // في HTTP Context: created_by أدق من auth()->id()
        $actorId = $payment->created_by ?? auth()->id() ?? 1;

        // بناء الـ DTO من الحدث
        $data = LedgerEntryData::forStudentPayment(
            payment: $payment,
            createdBy: $actorId,
        );

        // التسجيل (Idempotent: لن يتكرر إذا كان موجوداً)
        $this->ledgerService->record($data);
    }
}
