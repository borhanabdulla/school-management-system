<?php

namespace Tests\Feature\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Services\FinanceLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CacheKeyYearIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected FinanceLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FinanceLookupService::class);
        Cache::flush();
    }

    public function test_stats_keys_are_year_specific()
    {
        // Use distinct statuses to avoid SQLite partial index issues if environment is old
        $yearA = AcademicYear::factory()->create(['status' => 'closed']);
        $yearB = AcademicYear::factory()->create(['status' => 'archived']);

        // 1. Generate stats for Year A
        // We need some data to make stats meaningful, but cache key logic doesn't depend on data quantity.
        // Actually, let's just checking the keys implicitly by calling the service.

        $statsA = $this->service->getFinanceStatistics($yearA->id);
        $statsB = $this->service->getFinanceStatistics($yearB->id);

        // 2. Verify Cache has both keys
        // Method statsKey is private, so we reconstruct it.
        $keyA = "finance:year:{$yearA->id}:stats";
        $keyB = "finance:year:{$yearB->id}:stats";

        // Access cache store. If tags are used, we must use them.
        // FinanceLookupService uses tag 'finance'.
        $store = Cache::tags(['finance']);

        $this->assertTrue($store->has($keyA), "Cache for Year A should exist");
        $this->assertTrue($store->has($keyB), "Cache for Year B should exist");
    }

    public function test_invalidation_is_isolated_to_year()
    {
        $yearA = AcademicYear::factory()->create(['status' => 'closed']);
        $yearB = AcademicYear::factory()->create(['status' => 'archived']);

        // 1. Prime Cache
        $this->service->getFinanceStatistics($yearA->id);
        $this->service->getFinanceStatistics($yearB->id);

        $store = Cache::tags(['finance']);
        $keyA = "finance:year:{$yearA->id}:stats";
        $keyB = "finance:year:{$yearB->id}:stats";

        $this->assertTrue($store->has($keyA));
        $this->assertTrue($store->has($keyB));

        // 2. Trigger Invalidation for Year A via InvalidateStats
        $this->service->invalidateStats($yearA->id);

        // 3. Verify Only Year A is gone
        $this->assertFalse($store->has($keyA), "Cache for Year A should be invalidated");
        $this->assertTrue($store->has($keyB), "Cache for Year B should REMAIN valid");
    }

    public function test_invoice_creation_invalidates_only_relevant_year()
    {
        $yearA = AcademicYear::factory()->create(['status' => 'closed']);
        $yearB = AcademicYear::factory()->create(['status' => 'archived']);
        $student = Student::factory()->create();

        // Prime Cache
        $this->service->getFinanceStatistics($yearA->id);
        $this->service->getFinanceStatistics($yearB->id);

        $store = Cache::tags(['finance']);
        $keyA = "finance:year:{$yearA->id}:stats";
        $keyB = "finance:year:{$yearB->id}:stats";

        // Create Invoice for Year A
        // We depend on Events. Make sure Events are faked? No, we want real listeners.
        // But Invoice::create dispatches InvoiceCreated.

        $invoice = Invoice::factory()->create([
            'academic_year_id' => $yearA->id,
            'student_id' => $student->id
        ]);

        // Assert
        $this->assertFalse($store->has($keyA), "Creating invoice in Year A should invalidate Year A stats");
        $this->assertTrue($store->has($keyB), "Creating invoice in Year A should NOT invalidate Year B stats");
    }

    public function test_payment_invalidates_only_relevant_year()
    {
        $yearA = AcademicYear::factory()->create(['status' => 'closed']);
        $yearB = AcademicYear::factory()->create(['status' => 'archived']);
        $student = Student::factory()->create();

        $invoice = Invoice::factory()->create([
            'academic_year_id' => $yearA->id,
            'student_id' => $student->id,
            'total_amount' => 1000
        ]);

        // Prime Cache
        $this->service->getFinanceStatistics($yearA->id);
        $this->service->getFinanceStatistics($yearB->id);

        $store = Cache::tags(['finance']);
        $keyA = "finance:year:{$yearA->id}:stats";
        $keyB = "finance:year:{$yearB->id}:stats";

        // Create Payment on Year A Invoice
        // Need to authorize or mock Gate for Action? Listener listens to Event, Action dispatches it.
        // We can just manually dispatch event or use the Action. 
        // Using Action is better integration test.

        // Mock authorization
        $user = \App\Domains\Shared\Models\User::factory()->create();
        $this->actingAs($user);
        \Illuminate\Support\Facades\Gate::define('finance.record_payment', fn() => true);

        // Create a proper financial sponsor guardian
        $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
        $student->guardians()->attach($guardian->id, ['is_financial_sponsor' => true, 'relationship' => 'father']);

        $action = app(\App\Domains\Finance\Actions\RecordPaymentAction::class);
        $action->execute(\App\Domains\Finance\Data\PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $guardian->id,
            'amount' => 500,
            'method' => PaymentMethod::Cash,
        ]));

        // Assert
        $this->assertFalse($store->has($keyA), "Payment on Year A invoice should invalidate Year A stats");
        $this->assertTrue($store->has($keyB), "Payment on Year A should NOT invalidate Year B stats");
    }
}
