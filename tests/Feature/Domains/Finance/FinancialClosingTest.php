<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Actions\ApplyDiscountAction;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Services\YearEndReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FinancialClosingTest extends TestCase
{
    use RefreshDatabase;

    private YearEndReportService $service;
    private $year;
    private \App\Domains\Shared\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $recordPermission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
        $applyPermission = Permission::firstOrCreate(['name' => 'finance.apply_discount']);
        $this->user = \App\Domains\Shared\Models\User::factory()->create();
        $this->user->givePermissionTo([$recordPermission, $applyPermission]);
        $this->actingAs($this->user);

        $this->service = app(YearEndReportService::class);
        $this->year = AcademicYear::factory()->create(['status' => 'active']);
    }

    /** @test */
    public function it_generates_correct_year_summary()
    {
        // Setup Data
        // Student 1: Paid (Invoice 1000, Discount 200, Paid 800)
        $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
        $s1 = Student::factory()->create(['current_grade_id' => $grade->id]);
        $invoice1 = $this->createInvoice($s1, 1000);
        $this->applyDiscount($invoice1, 200);
        $this->pay($invoice1, 800);

        // Student 2: Partial (Invoice 1200, Paid 200)
        $s2 = Student::factory()->create();
        $invoice2 = $this->createInvoice($s2, 1200);
        $this->pay($invoice2, 200);

        // Student 3: Cancelled (Invoice 500, Cancelled)
        $s3 = Student::factory()->create();
        $invoice3 = $this->createInvoice($s3, 500);
        $invoice3->update(['status' => 'cancelled']);

        // Act
        $summary = $this->service->yearSummary($this->year->id);

        // Assert
        // Net Invoiced: 800 (s1) + 1200 (s2) = 2000. (s3 excluded)
        expect($summary['invoiced_net'])->toEqual(2000.0);

        // Discounts: 200 (s1). s3 discount if applied should be excluded if invoice cancelled?
        // Logic check: We applied discount to s1.
        expect($summary['total_discounts'])->toEqual(200.0);

        // Collected: 800 (s1) + 200 (s2) = 1000.
        expect($summary['collected'])->toEqual(1000.0);

        // Outstanding: 2000 - 1000 = 1000.
        expect($summary['outstanding'])->toEqual(1000.0);
    }

    /** @test */
    public function it_lists_blocked_students_correctly()
    {
        // Student 2 from above (Partial) should be blocked
        $s2 = Student::factory()->create();
        $invoice2 = $this->createInvoice($s2, 1200);
        $this->pay($invoice2, 200);

        // Student 4: Cleared with Override
        $s4 = Student::factory()->create();
        $invoice4 = $this->createInvoice($s4, 500);
        // Outstanding 500
        $user = \App\Domains\Shared\Models\User::factory()->create();
        DB::table('financial_clearance_overrides')->insert([
            'student_id' => $s4->id,
            'academic_year_id' => $this->year->id,
            'reason' => 'Admin Override',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $blocked = $this->service->blockedStudents($this->year->id);

        // S2 should be in list, S4 not
        $blockedIds = collect($blocked)->pluck('student_id');
        expect($blockedIds)->toContain($s2->id);
        expect($blockedIds)->not->toContain($s4->id);
    }

    // Helpers
    private function createInvoice($student, $amount)
    {
        // Ensure student has a financial sponsor
        if ($student->guardians()->wherePivot('is_financial_sponsor', true)->doesntExist()) {
            $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
            $student->guardians()->attach($guardian->id, [
                'relationship' => 'father',
                'is_financial_sponsor' => true,
                'is_emergency_contact' => true,
            ]);
        }

        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);
        // Use student's grade or create one if missing (should be set by factory usually)
        $gradeId = $student->current_grade_id;
        if (!$gradeId) {
            $grade = \App\Domains\Academic\Grade\Models\Grade::factory()->create();
            $student->update(['current_grade_id' => $grade->id]);
            $gradeId = $grade->id;
        }

        $fs = FeeStructure::firstOrCreate([
            'academic_year_id' => $this->year->id,
            'grade_id' => $gradeId,
            'fee_type_id' => $ft->id,
        ], [
            'amount' => $amount,
            'due_date' => now(),
        ]);

        return app(CreateInvoiceAction::class)->execute(InvoiceData::fromArray([
            'student_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $gradeId,
        ]));
    }

    private function applyDiscount($invoice, $amount)
    {
        $discount = Discount::create([
            'name' => 'Test Discount',
            'value' => $amount,
            'type' => 'fixed',
        ]);

        app(ApplyDiscountAction::class)->execute(
            $invoice->items->first()->id,
            $discount->id,
            'Test Reason'
        );
    }

    private function pay($invoice, $amount)
    {
        $invoice->refresh();
        app(RecordPaymentAction::class)->execute(PaymentData::fromArray([
            'invoice_id' => $invoice->id,
            'guardian_id' => $invoice->payer_guardian_id,
            'amount' => $amount,
            'method' => \App\Domains\Finance\Enums\PaymentMethod::Cash,
        ]));
    }
}
