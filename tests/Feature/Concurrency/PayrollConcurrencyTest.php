<?php

namespace Tests\Feature\Concurrency;

use App\Domains\HR\Payroll\Actions\MarkPayrollPaidAction;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Enums\PayoutMethod;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\Finance\Ledger\Models\LedgerEntry;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create();
    }

    private function createApprovedBatch(User $user): PayrollBatch
    {
        return PayrollBatch::create([
            'name' => 'Test Batch ' . uniqid(),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'year' => now()->year,
            'month' => now()->month,
            'status' => PayrollBatchStatus::Approved,
            'total_net' => 10000,
            'total_gross' => 12000,
            'total_deductions' => 2000,
            'employees_count' => 5,
            'approved_at' => now(),
            'approved_by' => $user->id,
        ]);
    }

    /** @test */
    public function it_prevents_double_payroll_paid_with_idempotency(): void
    {
        $user = $this->createUser();
        $batch = $this->createApprovedBatch($user);
        $action = app(MarkPayrollPaidAction::class);

        // الصرف الأول
        $result1 = $action->execute($batch, $user->id, PayoutMethod::Cash, 'REF-001');
        $this->assertEquals(PayrollBatchStatus::Paid, $result1->status);

        // الصرف الثاني (idempotent - يجب أن يُرجع نفس النتيجة بدون خطأ)
        $result2 = $action->execute($batch->fresh(), $user->id, PayoutMethod::Cash, 'REF-002');
        $this->assertEquals(PayrollBatchStatus::Paid, $result2->status);

        // التأكد من أن المسير صُرف مرة واحدة فقط
        $this->assertEquals(1, PayrollBatch::paid()->count());

        // التأكد من أن paid_at و payout_reference لم يتغيرا
        $batch->refresh();
        $this->assertEquals('REF-001', $batch->payout_reference);
    }

    /** @test */
    public function it_does_not_duplicate_ledger_entry_on_retry(): void
    {
        $user = $this->createUser();
        $batch = $this->createApprovedBatch($user);
        $action = app(MarkPayrollPaidAction::class);

        // الصرف الأول
        $action->execute($batch, $user->id, PayoutMethod::Cash);

        // الصرف الثاني (retry simulation)
        $action->execute($batch->fresh(), $user->id, PayoutMethod::Cash);

        // التأكد من أن هناك قيد Ledger واحد فقط
        $ledgerCount = LedgerEntry::where('external_key', "payroll_batch:{$batch->id}")->count();
        $this->assertEquals(1, $ledgerCount);
    }

    /** @test */
    public function it_throws_exception_when_paying_draft_batch(): void
    {
        $batch = PayrollBatch::create([
            'name' => 'Draft Batch',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'year' => now()->year,
            'month' => now()->month,
            'status' => PayrollBatchStatus::Draft,
            'total_net' => 1000,
            'total_gross' => 1200,
            'total_deductions' => 200,
            'employees_count' => 1,
        ]);

        $action = app(MarkPayrollPaidAction::class);

        $this->expectException(\App\Domains\HR\Payroll\Exceptions\InvalidWorkflowStateException::class);
        $action->execute($batch, 1);
    }

    /** @test */
    public function it_throws_exception_when_paying_frozen_batch(): void
    {
        $user = $this->createUser();
        $batch = PayrollBatch::create([
            'name' => 'Frozen Batch',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'year' => now()->year,
            'month' => now()->month,
            'status' => PayrollBatchStatus::Frozen,
            'total_net' => 1000,
            'total_gross' => 1200,
            'total_deductions' => 200,
            'employees_count' => 1,
            'frozen_at' => now(),
            'frozen_by' => $user->id,
        ]);

        $action = app(MarkPayrollPaidAction::class);

        $this->expectException(\App\Domains\HR\Payroll\Exceptions\InvalidWorkflowStateException::class);
        $action->execute($batch, $user->id);
    }
}
