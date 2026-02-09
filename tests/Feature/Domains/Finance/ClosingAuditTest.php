<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Services\FinancialLockService;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use App\Domains\Academic\Student\Models\Student;

class ClosingAuditTest extends TestCase
{
    use RefreshDatabase;

    private FinancialLockService $service;
    private $year;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FinancialLockService::class);
        $this->year = AcademicYear::factory()->create(['status' => 'active', 'financial_status' => 'open']);
        // Use shared User factory which returns correct instance for auth
        $permission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo($permission);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_creates_secure_audit_log_when_locking()
    {
        // 1. Setup Data to produce reports
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        $student = Student::factory()->create(['current_grade_id' => $grade->id]);
        $this->setupSponsor($student);

        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 1000, // Invoiced Net will be 1000
            'due_date' => now(),
        ]);

        app(CreateInvoiceAction::class)->execute(InvoiceData::fromArray([
            'student_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
        ]));

        // 2. Lock
        $this->service->lock($this->year, $this->user, 'Final Closing');

        // 3. Assert Year Status
        $this->year->refresh();
        expect($this->year->financial_status)->toEqual('closed');
        expect($this->year->financial_closed_by)->toEqual($this->user->id);

        // 4. Assert Audit Log exists and is valid
        $this->assertDatabaseHas('financial_closing_logs', [
            'academic_year_id' => $this->year->id,
            'closed_by' => $this->user->id,
            'reason' => 'Final Closing',
            'report_version' => 'v1',
        ]);

        $log = \App\Domains\Finance\Models\FinancialClosingLog::where('academic_year_id', $this->year->id)->first();

        // Assert Snapshot Content
        $snapshot = $log->report_snapshot;
        expect($snapshot['summary']['invoiced_net'])->toEqual(1000);
        expect($snapshot['summary']['outstanding'])->toEqual(1000); // 0 collected

        // Assert Hash
        $computedHash = hash('sha256', json_encode($snapshot));
        expect($log->snapshot_hash)->toEqual($computedHash);
    }

    /** @test */
    public function it_is_idempotent()
    {
        // Lock once
        $this->service->lock($this->year, $this->user);

        // Lock again
        $this->service->lock($this->year, $this->user);

        // Should only be one log
        expect(\App\Domains\Finance\Models\FinancialClosingLog::count())->toEqual(1);
    }

    /** @test */
    public function snapshot_remains_unchanged_after_post_closing_payment()
    {
        // 1. Setup and Invoice
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        $student = Student::factory()->create(['current_grade_id' => $grade->id]);
        $this->setupSponsor($student);

        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 1000,
            'due_date' => now(),
        ]);

        $invoice = app(CreateInvoiceAction::class)->execute(InvoiceData::fromArray([
            'student_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $grade->id,
        ]));

        // 2. Lock (Snapshot taken here: collected = 0, outstanding = 1000)
        $this->service->lock($this->year, $this->user);

        $log = \App\Domains\Finance\Models\FinancialClosingLog::where('academic_year_id', $this->year->id)->first();
        expect($log->report_snapshot['summary']['collected'])->toEqual(0);
        expect($log->report_snapshot['summary']['outstanding'])->toEqual(1000);

        // 3. Make Post-Closing Payment
        $invoice->refresh();
        app(RecordPaymentAction::class)->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $invoice->payer_guardian_id,
            'amount' => 500,
            'method' => \App\Domains\Finance\Enums\PaymentMethod::Cash,
        ]));

        // 4. Verify Live Reports CHANGED
        $liveSummary = app(\App\Domains\Finance\Services\YearEndReportService::class)->yearSummary($this->year->id);
        expect($liveSummary['collected'])->toEqual(500);
        expect($liveSummary['outstanding'])->toEqual(500);

        // 5. Verify Snapshotted Reports UNCHANGED
        $log->refresh();
        expect($log->report_snapshot['summary']['collected'])->toEqual(0);
        expect($log->report_snapshot['summary']['outstanding'])->toEqual(1000); // Should still say 1000 outstanding at closing time

        // Hash should still match original snapshot
        $computedHash = hash('sha256', json_encode($log->report_snapshot));
        expect($log->snapshot_hash)->toEqual($computedHash);
    }

    private function setupSponsor($student)
    {
        $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
        $student->guardians()->attach($guardian->id, [
            'relationship' => 'father',
            'is_financial_sponsor' => true,
            'is_emergency_contact' => true,
        ]);
    }
}
