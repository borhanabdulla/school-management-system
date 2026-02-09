<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\PaymentStatus;
use App\Domains\Finance\Events\PaymentCancelled;
use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Services\FinancialLockService;
use App\Domains\Finance\Services\InvoiceTotalsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CancelPaymentAction
{
    public function __construct(
        private InvoiceTotalsService $invoiceTotalsService,
        private FinancialLockService $lockService
    ) {
    }

    /**
     * إلغاء دفعة مالية
     *
     * @param int $paymentId ID الدفعة
     * @param string $reason سبب الإلغاء
     * @return Payment الدفعة بعد التحديث
     * @throws ValidationException إذا كانت الدفعة ملغاة مسبقاً
     */
    public function execute(int $paymentId, string $reason): Payment
    {
        // 0. Authorization
        Gate::authorize('finance.cancel_payment');

        return DB::transaction(function () use ($paymentId, $reason) {
            // 1. جلب الدفعة مع القفل لمنع Race Conditions
            $payment = Payment::with('invoice')->lockForUpdate()->find($paymentId);

            if (!$payment) {
                throw ValidationException::withMessages([
                    'payment_id' => 'الدفعة غير موجودة',
                ]);
            }

            // 1.5 Check Financial Lock on the Academic Year
            // Relaxed for PR-F1: Collection/Correction allowed even if closed.
            // $this->lockService->ensureOpen($payment->invoice->academic_year_id);

            // 2. التحقق من الحالة الحالية
            if ($payment->status === PaymentStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'payment_id' => 'هذه الدفعة ملغاة بالفعل',
                ]);
            }

            // 3. تحديث الحالة
            $payment->update([
                'status' => PaymentStatus::Cancelled,
                'cancel_reason' => $reason,
                'cancelled_by' => auth()->id(), // المستخدم الحالي
                'cancelled_at' => now(),
            ]);

            // 4. إعادة حساب الفاتورة (لأن المبلغ المدفوع تغير)
            $this->invoiceTotalsService->recalculate($payment->invoice);

            // 5. Dispatch Event
            PaymentCancelled::dispatch($payment, $payment->invoice);

            return $payment;
        });
    }
}
