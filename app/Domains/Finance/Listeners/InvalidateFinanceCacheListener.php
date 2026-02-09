<?php

declare(strict_types=1);

namespace App\Domains\Finance\Listeners;

use App\Domains\Finance\Events\DiscountApplied;
use App\Domains\Finance\Events\InvoiceCreated;
use App\Domains\Finance\Events\PaymentReceived;
use App\Domains\Finance\Services\FinanceLookupService;

/**
 * InvalidateFinanceCacheListener - مستمع لإبطال كاش المالية
 * 
 * يستمع لأحداث:
 * - InvoiceCreated: إنشاء فاتورة جديدة
 * - PaymentReceived: تسجيل دفعة
 * - DiscountApplied: تطبيق خصم
 * 
 * ويقوم بإبطال الكاش المناسب في FinanceLookupService
 */
class InvalidateFinanceCacheListener
{
    public function __construct(
        private FinanceLookupService $lookupService
    ) {
    }

    /**
     * إبطال الكاش عند إنشاء فاتورة
     */
    public function handleInvoiceCreated(InvoiceCreated $event): void
    {
        $invoice = $event->invoice;

        $this->lookupService->invalidateForStudent($invoice->student_id, $invoice->academic_year_id);
        $this->lookupService->invalidateStats($invoice->academic_year_id);
    }

    /**
     * إبطال الكاش عند تسجيل دفعة
     */
    public function handlePaymentReceived(PaymentReceived $event): void
    {
        $invoice = $event->invoice;

        $this->lookupService->invalidateForStudent($invoice->student_id, $invoice->academic_year_id);
        $this->lookupService->invalidateStats($invoice->academic_year_id);
        $this->lookupService->invalidateRecentPayments($invoice->academic_year_id);
    }

    /**
     * إبطال الكاش عند تطبيق خصم
     */
    public function handleDiscountApplied(DiscountApplied $event): void
    {
        $invoice = $event->invoice;

        $this->lookupService->invalidateForStudent($invoice->student_id, $invoice->academic_year_id);
        $this->lookupService->invalidateStats($invoice->academic_year_id);
    }

    /**
     * إبطال الكاش عند إلغاء دفعة
     */
    public function handlePaymentCancelled(\App\Domains\Finance\Events\PaymentCancelled $event): void
    {
        $invoice = $event->invoice;

        $this->lookupService->invalidateForStudent($invoice->student_id, $invoice->academic_year_id);
        $this->lookupService->invalidateStats($invoice->academic_year_id);
        $this->lookupService->invalidateRecentPayments($invoice->academic_year_id);
    }
}
