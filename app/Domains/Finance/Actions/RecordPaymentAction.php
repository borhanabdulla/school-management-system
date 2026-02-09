<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Events\PaymentReceived;
use App\Domains\Finance\Exceptions\PaymentExceedsInvoiceException;
use App\Domains\Finance\Exceptions\DuplicatePaymentException;
use App\Domains\Finance\Exceptions\AcademicYearClosedException;
use App\Domains\Finance\Exceptions\InvoicePayerMismatchException;
use App\Domains\Finance\Exceptions\GuardianNotFinancialSponsorException;
use App\Domains\Finance\Services\InvoiceTotalsService;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * RecordPaymentAction - تسجيل دفعة مالية
 * 
 * تُسجل دفعة على فاتورة مع التحقق من:
 * - السنة الدراسية مفتوحة
 * - الولي هو المسؤول المالي
 * - المبلغ لا يتجاوز المتبقي
 * 
 * @example
 * $action = app(RecordPaymentAction::class);
 * $payment = $action->execute(PaymentData::fromArray([
 *     'invoice_id' => 1,
 *     'guardian_id' => 5,
 *     'amount' => 500.00,
 *     'method' => \App\Domains\Finance\Enums\PaymentMethod::Cash,
 * ]));
 */
class RecordPaymentAction
{
    public function __construct(
        private InvoiceTotalsService $invoiceTotalsService
    ) {
    }

    public function execute(PaymentData $data): Payment
    {
        Gate::authorize('finance.record_payment');

        return DB::transaction(function () use ($data) {
            $invoice = Invoice::with(['student.guardians', 'academicYear'])
                ->lockForUpdate()
                ->findOrFail($data->invoiceId);

            // 1. (Removed) Allow payments for closed years (Arrears Collection)

            // 2. التحقق من أهلية الدفع
            $this->enforcePaymentEligibility($invoice, $data->guardianId);

            // 3. التحقق من عدم تكرار المعاملة
            if ($data->transactionReference) {
                $exists = Payment::where('transaction_reference', $data->transactionReference)->exists();
                if ($exists) {
                    throw new DuplicatePaymentException($data->transactionReference);
                }
            }

            // 4. التحقق من المبلغ المتبقي
            $remainingAmount = $invoice->total_amount - $invoice->paid_amount;
            if ($data->amount > $remainingAmount) {
                throw new PaymentExceedsInvoiceException($data->amount, $remainingAmount);
            }

            // 5. إنشاء الدفعة
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'guardian_id' => $data->guardianId,
                'amount' => $data->amount,
                'method' => $data->method,
                'transaction_reference' => $data->transactionReference,
                'paid_at' => now(),
                'notes' => $data->notes,
                'created_by' => auth()->id(),
            ]);

            // 6. تحديث الفاتورة عبر الخدمة الموحدة
            $this->invoiceTotalsService->recalculate($invoice);

            // 7. إطلاق الحدث
            PaymentReceived::dispatch($payment, $invoice->fresh());

            return $payment;
        });
    }

    /**
     * التحقق من أهلية الدفع (Logic PR1)
     * 
     * - إذا كانت الفاتورة مثبتة لدافع معين: يجب أن يتطابق
     * - إذا لم تكن مثبتة: يجب أن يكون الكفيل مالياً، ويتم تثبيته تلقائياً
     */
    private function enforcePaymentEligibility(Invoice $invoice, int $guardianId): void
    {
        // 1. الفاتورة لها دافع مثبت
        if ($invoice->payer_guardian_id) {
            if ($invoice->payer_guardian_id !== $guardianId) {
                throw new InvoicePayerMismatchException($invoice->payer_guardian_id, $guardianId);
            }
            return;
        }

        // 2. الفاتورة ليس لها دافع (قديمة أو خطأ) -> Fallback Logic
        $isFinancialSponsor = $invoice->student->guardians()
            ->wherePivot('guardian_id', $guardianId)
            ->wherePivot('is_financial_sponsor', true)
            ->exists();

        if (!$isFinancialSponsor) {
            throw new GuardianNotFinancialSponsorException($guardianId);
        }

        // Auto-fix: تثبيت الدافع على الفاتورة بأثر رجعي
        $invoice->update([
            'payer_guardian_id' => $guardianId,
            'payer_set_at' => now(),
            'payer_set_by' => auth()->id(), // قد يكون null في الـ seeder أو job
        ]);
    }
}
