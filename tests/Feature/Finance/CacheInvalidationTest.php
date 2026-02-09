<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Services\FinanceLookupService;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Enums\GuardianRelationship;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Mockery;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Define permission for testing
        Gate::define('finance.record_payment', function ($user) {
            return true;
        });

        // Setup basic requirements
        AcademicYear::create([
            'name' => 'Year 2025',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    public function test_cache_is_invalidated_after_payment_using_observer()
    {
        // 1. Setup Data
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();

        if (!$student->guardians()->where('guardian_id', $guardian->id)->exists()) {
            $student->guardians()->attach($guardian->id, [
                'is_financial_sponsor' => true,
                'relationship' => GuardianRelationship::Father,
            ]);
        }

        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'payer_guardian_id' => $guardian->id, // Explicitly set payer
            'total_amount' => 1000,
            'paid_amount' => 0,
            'academic_year_id' => AcademicYear::first()->id,
        ]);

        // 2. Mock the FinanceLookupService
        // We expect invalidateInvoice to be called with the invoice ID
        $mock = $this->mock(FinanceLookupService::class);
        $mock->shouldReceive('invalidateInvoice')
            ->once()
            ->with($invoice->id);

        // Allow other calls (invalidateForStudent, invalidateStats, etc) without error
        $mock->shouldIgnoreMissing();

        // Act: Record Payment
        $admin = User::factory()->create();
        $this->actingAs($admin);

        app(RecordPaymentAction::class)->execute(
            PaymentData::fromArray([
                'invoice_id' => $invoice->id,
                'guardian_id' => $guardian->id,
                'amount' => 500,
                'method' => PaymentMethod::Cash,
            ])
        );

        // Assert: Mockery verification happens automatically by TestCase tearDown
    }
}
