<?php

declare(strict_types=1);

namespace App\Domains\Finance\Observers;

use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Services\FinanceLookupService;

/**
 * PaymentObserver - مراقب الدفعات المالية
 * 
 * Purpose: إبطال الكاش تلقائياً عند أي تغيير على الدفعات
 * 
 * متى نستخدم Observer؟
 * - للعمليات التلقائية المرتبطة بدورة حياة الموديل (created, updated, deleted)
 * - لإبطال الكاش
 * - للعمليات التي MUST تحدث (لا يمكن تعطيلها)
 */
class PaymentObserver
{
    public function __construct(
        protected FinanceLookupService $lookupService
    ) {
    }

    /**
     * بعد إنشاء دفعة جديدة
     */
    public function created(Payment $payment): void
    {
        $this->invalidateRelatedCache($payment);
    }

    /**
     * بعد تحديث دفعة (مثل الإلغاء)
     */
    public function updated(Payment $payment): void
    {
        $this->invalidateRelatedCache($payment);
    }

    /**
     * إبطال الكاش المرتبط بالدفعة
     */
    protected function invalidateRelatedCache(Payment $payment): void
    {
        // إبطال كاش الفاتورة
        if ($payment->invoice_id) {
            $this->lookupService->invalidateInvoice($payment->invoice_id);
        }

        // إبطال كاش الطالب (إذا كانت الفاتورة محملة)
        if ($payment->invoice && $payment->invoice->student_id) {
            $this->lookupService->invalidateStudent($payment->invoice->student_id);
        }

        // إبطال كاش السنة الأكاديمية
        if ($payment->invoice && $payment->invoice->academic_year_id) {
            $this->lookupService->invalidateAcademicYear($payment->invoice->academic_year_id);
        }
    }
}
