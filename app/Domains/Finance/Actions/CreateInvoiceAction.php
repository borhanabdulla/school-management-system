<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoiceItem;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Events\InvoiceCreated;
use App\Domains\Finance\Services\InvoiceTotalsService;
use App\Domains\Finance\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;

/**
 * CreateInvoiceAction - إنشاء فاتورة جديدة
 * 
 * تُنشئ فاتورة لطالب بناءً على هيكل الرسوم للصف والسنة الدراسية
 * 
 * @example
 * $action = app(CreateInvoiceAction::class);
 * $invoice = $action->execute(InvoiceData::fromArray([
 *     'student_id' => 1,
 *     'academic_year_id' => 1,
 *     'grade_id' => 1,
 * ]));
 */
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Services\PayerResolverService;

/**
 * CreateInvoiceAction - إنشاء فاتورة جديدة
 * 
 * تُنشئ فاتورة لطالب بناءً على هيكل الرسوم للصف والسنة الدراسية
 * مع تثبيت المسؤول المالي (Payer Snapshot).
 * 
 * @example
 * $action = app(CreateInvoiceAction::class);
 * $invoice = $action->execute(InvoiceData::fromArray([
 *     'student_id' => 1,
 *     'academic_year_id' => 1,
 *     'grade_id' => 1,
 * ]));
 */
class CreateInvoiceAction
{
    public function __construct(
        private InvoiceTotalsService $totalsService,
        private PayerResolverService $payerResolver,
        private \App\Domains\Finance\Services\FinancialLockService $lockService
    ) {
    }

    public function execute(InvoiceData $data): ?Invoice
    {
        return DB::transaction(function () use ($data) {
            // Guard: Financial Lock
            $this->lockService->ensureOpen($data->academicYearId);

            // 0. تحديد المسؤول المالي (Payer Snapshot)
            $student = Student::findOrFail($data->studentId);
            $payer = $this->payerResolver->resolve($student);

            // 1. جلب هيكل الرسوم
            $feeStructures = FeeStructure::where('academic_year_id', $data->academicYearId)
                ->where('grade_id', $data->gradeId)
                ->get();

            if ($feeStructures->isEmpty()) {
                return null;
            }

            // 2. حساب المجموع
            $totalAmount = $feeStructures->sum('amount');

            // 3. إنشاء الفاتورة
            $invoice = Invoice::create([
                'invoice_number' => $this->generateTemporaryNumber(),
                'student_id' => $data->studentId,
                'academic_year_id' => $data->academicYearId,
                'issue_date' => now(),
                'due_date' => $data->dueDate ?? $feeStructures->first()?->due_date ?? now()->addMonth(),
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => InvoiceStatus::Unpaid,
                // تثبيت الدافع
                'payer_guardian_id' => $payer->id,
                'payer_set_at' => now(),
                'payer_set_by' => auth()->id(),
            ]);

            // 4. تحديث رقم الفاتورة
            $invoice->update([
                'invoice_number' => $this->generateInvoiceNumber($invoice->id)
            ]);

            // 5. إنشاء بنود الفاتورة
            if ($data->generateItems) {
                $this->createInvoiceItems($invoice, $feeStructures);
            }

            // 6. إطلاق الحدث - يتم تلقائياً عبر الموديل (Source of Truth)
            // InvoiceCreated::dispatch($invoice);

            return $invoice;
        });
    }

    /**
     * إنشاء بنود الفاتورة من هيكل الرسوم
     */
    private function createInvoiceItems(Invoice $invoice, $feeStructures): void
    {
        foreach ($feeStructures as $fee) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'fee_type_id' => $fee->fee_type_id,
                'amount' => $fee->amount,
                'discount_id' => null, // PR0: بدون خصومات
            ]);
        }
    }

    /**
     * توليد رقم مؤقت قبل الحفظ
     */
    private function generateTemporaryNumber(): string
    {
        return 'TEMP-' . uniqid();
    }

    /**
     * توليد رقم فاتورة فريد
     */
    private function generateInvoiceNumber(int $id): string
    {
        $year = date('Y');
        return 'INV-' . $year . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }
}
