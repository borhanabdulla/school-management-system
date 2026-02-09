<?php

namespace Tests\Feature\Ledger;

use App\Domains\Finance\Ledger\Data\LedgerEntryData;
use App\Domains\Finance\Ledger\Enums\LedgerCategory;
use App\Domains\Finance\Ledger\Enums\LedgerDirection;
use App\Domains\Finance\Ledger\Enums\LedgerStatus;
use App\Domains\Finance\Ledger\Models\LedgerEntry;
use App\Domains\Finance\Ledger\Services\LedgerService;
use App\Domains\Shared\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerBalanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LedgerService $ledgerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->ledgerService = app(LedgerService::class);
    }

    /** @test */
    public function it_calculates_net_cash_correctly()
    {
        // Arrange: Create some entries
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // Income entries
        $this->createEntry(LedgerDirection::In, 10000, 'payment:1', $start->copy()->addDays(1));
        $this->createEntry(LedgerDirection::In, 5000, 'payment:2', $start->copy()->addDays(5));

        // Expense entries
        $this->createEntry(LedgerDirection::Out, 3000, 'payroll_batch:1', $start->copy()->addDays(10));

        // Act
        $summary = $this->ledgerService->getCashSummaryForPeriod($start, $end);

        // Assert
        $this->assertEquals(15000, $summary['total_in']);
        $this->assertEquals(3000, $summary['total_out']);
        $this->assertEquals(12000, $summary['net_cash']);
    }

    /** @test */
    public function it_excludes_cancelled_entries_from_balance()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // Create and then cancel an entry
        $entry = $this->createEntry(LedgerDirection::In, 5000, 'payment:cancelled', $start->copy()->addDays(1));

        $this->ledgerService->cancelByExternalKey(
            'payment:cancelled',
            $this->admin->id,
            'اختبار الإلغاء'
        );

        // Create a valid entry
        $this->createEntry(LedgerDirection::In, 10000, 'payment:valid', $start->copy()->addDays(2));

        // Act
        $summary = $this->ledgerService->getCashSummaryForPeriod($start, $end);

        // Assert: Only the valid entry counts
        $this->assertEquals(10000, $summary['total_in']);
        $this->assertEquals(10000, $summary['net_cash']);
    }

    /** @test */
    public function it_cancels_entry_and_records_reason()
    {
        $this->createEntry(LedgerDirection::In, 5000, 'payment:to-cancel', now());

        // Act
        $cancelled = $this->ledgerService->cancelByExternalKey(
            'payment:to-cancel',
            $this->admin->id,
            'طلب المستخدم الإلغاء'
        );

        // Assert
        $this->assertNotNull($cancelled);
        $this->assertEquals(LedgerStatus::Cancelled, $cancelled->status);
        $this->assertEquals($this->admin->id, $cancelled->cancelled_by);
        $this->assertEquals('طلب المستخدم الإلغاء', $cancelled->cancel_reason);
        $this->assertNotNull($cancelled->cancelled_at);
    }

    private function createEntry(
        LedgerDirection $direction,
        float $amount,
        string $externalKey,
        Carbon $date
    ): LedgerEntry {
        $category = $direction === LedgerDirection::In
            ? LedgerCategory::StudentPayment
            : LedgerCategory::PayrollPayout;

        $data = new LedgerEntryData(
            entryDate: $date,
            direction: $direction,
            amount: $amount,
            category: $category,
            externalKey: $externalKey,
            sourceType: 'App\Models\MockSource',
            sourceId: rand(1, 1000),
            createdBy: $this->admin->id,
        );

        return $this->ledgerService->record($data);
    }
}
