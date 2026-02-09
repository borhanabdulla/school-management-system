<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\InvoiceItem;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\DiscountApplication;
use App\Domains\Finance\Services\InvoiceTotalsService;
use App\Domains\Finance\Exceptions\DiscountExceedsItemAmountException;
use App\Domains\Finance\Exceptions\DiscountAlreadyAppliedException;
use App\Domains\Finance\Exceptions\DiscountCreatesOverpaymentException;
use App\Domains\Finance\Events\DiscountApplied;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * ApplyDiscountAction - تطبيق خصم على بند في الفاتورة
 * 
 * يطبق سياسة "خصم واحد لكل بند" (Single Discount).
 * يعيد حساب إجمالي الفاتورة بعد التطبيق.
 */
class ApplyDiscountAction
{
    public function __construct(
        private InvoiceTotalsService $totalsService,
        private \App\Domains\Finance\Services\FinancialLockService $lockService
    ) {
    }

    public function execute(int $invoiceItemId, int $discountId, ?string $reason = null): DiscountApplication
    {
        Gate::authorize('finance.apply_discount');

        return DB::transaction(function () use ($invoiceItemId, $discountId, $reason) {
            $item = InvoiceItem::with(['invoice.academicYear'])->lockForUpdate()->findOrFail($invoiceItemId);

            // 0. التحقق من الإقفال المالي والسنة الدراسية
            if ($item->invoice->academicYear->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Closed) {
                throw new \App\Domains\Finance\Exceptions\AcademicYearClosedException($item->invoice->academicYear->name);
            }
            // Financial Lock Check
            $this->lockService->ensureOpen($item->invoice->academic_year_id);

            $discount = Discount::findOrFail($discountId);

            // 1. التحقق من عدم وجود خصم مسبق (Stacking Policy)
            if ($item->discountApplications()->exists()) {
                throw new DiscountAlreadyAppliedException($invoiceItemId);
            }

            // 2. حساب قيمة الخصم
            $discountAmount = match ($discount->type) {
                'percentage' => ($item->amount * $discount->value) / 100,
                'fixed' => $discount->value,
                default => 0,
            };
            $discountAmount = (float) $discountAmount;
            $itemAmount = (float) $item->amount;

            // 3. التحقق من عدم تجاوز قيمة البند
            if ($discountAmount > $itemAmount) {
                throw new DiscountExceedsItemAmountException($discountAmount, $itemAmount);
            }

            // 4. التحقق من عدم تجاوز المدفوع للصافي الجديد (Strict Overpayment Prevention)
            $currentPaid = $item->invoice->paid_amount;
            // $newInvoiceTotal = $this->totalsService->calculateNetTotal($item->invoice); // Need to calculate POTENTIAL total

            // Simulating the new total: Current Total - This Discount Amount
            // ALERT: The InvoiceTotalsService calculates based on DB records.
            // Since we insert DiscountApplication AFTER this check in the code flow (usually),
            // actually we should calculate:
            // Current Net - Discount Amount.

            $currentNet = (float) $item->invoice->total_amount;
            $potentialNet = $currentNet - $discountAmount;

            if ($currentPaid > $potentialNet) {
                throw new DiscountCreatesOverpaymentException($currentPaid, $potentialNet);
            }

            // 5. إنشاء سجل الخصم (Audit)
            $application = $item->discountApplications()->create([
                'discount_id' => $discountId,
                'applied_amount' => $discountAmount,
                'applied_by' => auth()->id(), // يفضل تمريره كمتغير
                'applied_at' => now(),
                'reason' => $reason,
            ]);

            // 6. تحديث إجمالي الفاتورة (Single Source of Truth)
            $this->totalsService->recalculate($item->invoice);

            // 7. إطلاق حدث الخصم لإبطال الكاش
            DiscountApplied::dispatch($discount, $item->invoice->fresh());

            return $application;
        });
    }
}
