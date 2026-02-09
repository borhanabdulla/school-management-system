<?php

namespace Tests\Feature\Journeys;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Services\FinanceLookupService;
use App\Domains\Shared\Models\User;
use Database\Seeders\EducationalStructureSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FinanceYearContextTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private $student;
    private $yearA; // 2023-2024
    private $yearB; // 2024-2025

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedActiveAcademicYearForDate(now());
        school()->invalidateYear();
        $this->seed([EducationalStructureSeeder::class, RoleSeeder::class]);

        $this->accountant = User::factory()->create();
        $this->accountant->givePermissionTo('finance.record_payment');

        // Create 2 Years
        $this->yearA = AcademicYear::factory()->create(['name' => '2023-2024', 'status' => 'closed']);
        $this->yearB = AcademicYear::query()->where('status', 'active')->first();

        $this->student = \App\Domains\Academic\Student\Models\Student::factory()->create();
    }

    /** @test */
    public function financial_status_is_strictly_year_bound()
    {
        // 1. Create Debt in Year A
        $invoiceA = Invoice::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->yearA->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Unpaid
        ]);

        $service = app(FinanceLookupService::class);

        // 2. Check Status for Year A (Should be RED/Unpaid)
        $statusA = $service->getStudentFinancialStatusForYear($this->student->id, $this->yearA->id);
        $this->assertEquals(InvoiceStatus::Unpaid->value, $statusA['status']);
        $this->assertEquals(1000, $statusA['amount']);

        // 3. Check Status for Year B (Should be GREEN/Paid - assuming no carry over logic yet)
        $statusB = $service->getStudentFinancialStatusForYear($this->student->id, $this->yearB->id);
        $this->assertEquals(InvoiceStatus::Paid->value, $statusB['status']);
        $this->assertEquals(0, $statusB['amount']);

        // 4. Check Global Status (Should be RED/Unpaid because of Year A)
        $statusGlobal = $service->getStudentFinancialStatusAnyYear($this->student->id);
        $this->assertEquals(InvoiceStatus::Unpaid->value, $statusGlobal['status']);
    }

    /** @test */
    public function promotion_is_blocked_only_by_relevant_year_debt()
    {
        // 1. Debt in Year A
        Invoice::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->yearA->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        // Mock Promotion Dependencies (simplifying logic to focus on financial check)
        // In reality, we'd use the full PromotionService, but checking the LookupService logic 
        // that PromotionService USES is sufficient for this architectural verify.

        $service = app(FinanceLookupService::class);

        // Simulating Promotion FROM Year A
        $statusFromA = $service->getStudentFinancialStatusForYear($this->student->id, $this->yearA->id);
        $this->assertNotEquals(InvoiceStatus::Paid->value, $statusFromA['status']);
        // Outcome: BLOCKED

        // Simulating Promotion FROM Year B (Imagine we skipped A or came new)
        $statusFromB = $service->getStudentFinancialStatusForYear($this->student->id, $this->yearB->id);
        $this->assertEquals(InvoiceStatus::Paid->value, $statusFromB['status']);
        // Outcome: ALLOWED (despite debt in A)
    }

    /** @test */
    public function cache_invalidation_actually_clears_tagged_data()
    {
        $service = app(FinanceLookupService::class);

        // 1. Cache Params
        $invoice = Invoice::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->yearB->id,
            'total_amount' => 500
        ]);

        // 2. Prime the Cache
        $service->getStudentInvoicesForYear($this->student->id, $this->yearB->id);
        $key = "finance:student:{$this->student->id}:year:{$this->yearB->id}:invoices";

        // Verify it exists (if we could check store directly, but we rely on behavior)
        // Let's modify DB directly to see if Service returns old data
        $invoice->update(['total_amount' => 999]); // DB changed

        $cachedResult = $service->getStudentInvoicesForYear($this->student->id, $this->yearB->id);
        $this->assertEquals(500, $cachedResult->first()->total_amount); // Should adhere to Cache (500)

        // 3. Trigger Invalidation (The Fix check)
        $service->invalidateForStudent($this->student->id, $this->yearB->id);

        // 4. Fetch again -> Should see 999
        $freshResult = $service->getStudentInvoicesForYear($this->student->id, $this->yearB->id);
        $this->assertEquals(999, $freshResult->first()->total_amount);
    }
}
