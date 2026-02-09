<?php

namespace Tests\Feature\Ledger;

use App\Domains\Finance\Events\PaymentCancelled;
use App\Domains\Finance\Events\PaymentReceived;
use App\Domains\Finance\Ledger\Enums\LedgerStatus;
use App\Domains\Finance\Ledger\Models\LedgerEntry;
use App\Domains\Finance\Ledger\Services\LedgerService;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerPaymentCancellationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_cancels_ledger_entry_when_payment_is_cancelled(): void
    {
        // 1. إنشاء Payment و Invoice
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'status' => 'posted',
        ]);

        // 2. تسجيل الدفعة في الـ Ledger (عبر Event)
        PaymentReceived::dispatch($payment, $invoice);

        // تأكيد وجود القيد
        $this->assertDatabaseHas('ledger_entries', [
            'external_key' => "payment:{$payment->id}",
            'status' => 'posted',
        ]);

        // 3. إلغاء الدفعة
        $payment->update([
            'status' => 'cancelled',
            'cancelled_by' => 1,
            'cancel_reason' => 'طلب العميل',
            'cancelled_at' => now(),
        ]);

        PaymentCancelled::dispatch($payment, $invoice);

        // 4. تأكيد إلغاء القيد في الـ Ledger
        $this->assertDatabaseHas('ledger_entries', [
            'external_key' => "payment:{$payment->id}",
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_returns_correct_net_cash_after_cancellation(): void
    {
        $ledgerService = app(LedgerService::class);

        // 1. إنشاء Payment وتسجيله
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 500,
            'status' => 'posted',
        ]);

        PaymentReceived::dispatch($payment, $invoice);

        // تأكيد Net = 500
        $summary = $ledgerService->getCashSummaryForPeriod(
            now()->startOfDay(),
            now()->endOfDay()
        );
        $this->assertEquals(500, $summary['net_cash']);

        // 2. إلغاء الدفعة
        $payment->update([
            'status' => 'cancelled',
            'cancelled_by' => 1,
            'cancel_reason' => 'إلغاء',
            'cancelled_at' => now(),
        ]);

        PaymentCancelled::dispatch($payment, $invoice);

        // 3. تأكيد Net = 0 (القيد الملغى مستبعد)
        $summary = $ledgerService->getCashSummaryForPeriod(
            now()->startOfDay(),
            now()->endOfDay()
        );
        $this->assertEquals(0, $summary['net_cash']);
    }

    /** @test */
    public function it_records_cancellation_details_in_ledger(): void
    {
        // 0. إنشاء User للإلغاء
        $user = \App\Domains\Shared\Models\User::factory()->create();

        // 1. إنشاء Payment وتسجيله
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 750,
            'status' => 'posted',
        ]);

        PaymentReceived::dispatch($payment, $invoice);

        // 2. إلغاء الدفعة مع تفاصيل
        $payment->update([
            'status' => 'cancelled',
            'cancelled_by' => $user->id,
            'cancel_reason' => 'خطأ في المبلغ',
            'cancelled_at' => now(),
        ]);

        PaymentCancelled::dispatch($payment, $invoice);

        // 3. تأكيد تفاصيل الإلغاء
        $entry = LedgerEntry::where('external_key', "payment:{$payment->id}")->first();

        $this->assertNotNull($entry);
        $this->assertEquals(LedgerStatus::Cancelled, $entry->status);
        $this->assertEquals($user->id, $entry->cancelled_by);
        $this->assertEquals('خطأ في المبلغ', $entry->cancel_reason);
        $this->assertNotNull($entry->cancelled_at);
    }
}
