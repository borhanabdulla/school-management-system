<?php

namespace Tests\Feature\Payroll;

use App\Domains\HR\Payroll\Actions\ApprovePayrollAction;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Actions\MarkPayrollPaidAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use App\Domains\HR\Payroll\Enums\PayoutMethod;
use App\Domains\HR\Payroll\Events\PayrollBatchPaid;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Services\PayrollCacheService;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PayrollPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PayrollBatch $batch;
    private PayrollCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        $this->admin = User::factory()->create();
        $staff = Staff::factory()->create();
        Contract::factory()->create([
            'staff_id' => $staff->id,
            'start_date' => now()->startOfYear(),
            'basic_salary' => 5000,
        ]);

        $this->cacheService = new PayrollCacheService();

        // Create Batch
        $action = app(GeneratePayrollAction::class);
        $data = PayrollGenerationData::fromYearMonth(now()->year, now()->month);
        $this->batch = $action->execute($data, $this->admin->id);
    }

    /** @test */
    public function it_cannot_pay_unapproved_batch()
    {
        $action = app(MarkPayrollPaidAction::class);

        $this->expectException(\App\Domains\HR\Payroll\Exceptions\InvalidWorkflowStateException::class);

        $action->execute($this->batch, $this->admin->id, PayoutMethod::Cash);
    }

    /** @test */
    public function it_processes_payment_successfully_and_dispatches_event()
    {
        // 1. Move to Approved
        $this->batch->update(['status' => PayrollBatchStatus::Frozen]);
        app(ApprovePayrollAction::class)->execute($this->batch, $this->admin->id);

        Event::fake([PayrollBatchPaid::class]);

        // 2. Pay
        $action = app(MarkPayrollPaidAction::class);
        $paidBatch = $action->execute(
            $this->batch,
            $this->admin->id,
            PayoutMethod::BankTransfer,
            'REF-123'
        );

        // 3. Verify
        $this->assertEquals(PayrollBatchStatus::Paid, $paidBatch->status);
        $this->assertNotNull($paidBatch->paid_at);
        $this->assertEquals($this->admin->id, $paidBatch->paid_by);
        $this->assertEquals(PayoutMethod::BankTransfer, $paidBatch->payout_method);
        $this->assertEquals('REF-123', $paidBatch->payout_reference);

        Event::assertDispatched(PayrollBatchPaid::class, function ($event) use ($paidBatch) {
            return $event->batch->id === $paidBatch->id;
        });
    }

    /** @test */
    public function it_invalidates_cache_on_events()
    {
        // Simulate cached value
        $key = $this->cacheService->summaryKey($this->batch->id);
        Cache::put($key, 'cached_value', 60);

        $this->assertEquals('cached_value', Cache::get($key));

        // Trigger Event (Simulate Frozen for example)
        $this->batch->update(['status' => PayrollBatchStatus::Draft]); // Reset

        // Use real events for this test part or easier: rely on Listener integration.
        // Let's call the listener manually to verify its logic, or fire event.
        // Firing event is better integration test.

        \App\Domains\HR\Payroll\Events\PayrollBatchFrozen::dispatch($this->batch);

        // Assert Cache Cleared
        $this->assertNull(Cache::get($key));
    }
}
