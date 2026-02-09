<?php

namespace Tests\Feature\Domains\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Services\DiscountPolicySuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountPolicySuggestionTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private Grade $grade;
    private Guardian $sponsor;
    private FeeStructure $tuitionFee;
    private CreateInvoiceAction $createInvoiceAction;
    private DiscountPolicySuggestionService $suggestionService;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Basic Data
        $this->year = AcademicYear::factory()->create(['status' => 'active']);
        $this->grade = Grade::factory()->create();
        $this->sponsor = Guardian::factory()->create();

        // 2. Setup Fees (Tuition)
        $feeType = FeeType::create(['name' => 'Tuition Fees', 'is_active' => true]);
        $this->tuitionFee = FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
            'fee_type_id' => $feeType->id,
            'amount' => 1000.00,
            'due_date' => now()->addMonth(),
        ]);

        // 3. Setup Discount Definition
        Discount::create(['name' => 'Sibling Discount', 'type' => 'percentage', 'value' => 100]);

        $this->createInvoiceAction = app(CreateInvoiceAction::class);
        $this->suggestionService = app(DiscountPolicySuggestionService::class);
    }

    private function createStudentWithInvoice(): \App\Domains\Finance\Models\Invoice
    {
        $student = Student::factory()->create(['current_grade_id' => $this->grade->id]);

        // Link to sponsor
        $student->guardians()->attach($this->sponsor->id, [
            'is_financial_sponsor' => true,
            'relationship' => 'father'
        ]);

        return $this->createInvoiceAction->execute(InvoiceData::fromArray([
            'student_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));
    }

    /** @test */
    public function it_suggests_discount_for_third_student_of_same_payer()
    {
        // 1. Create 2 invoices for Payer (No suggestion yet)
        $invoice1 = $this->createStudentWithInvoice();
        $suggestions1 = $this->suggestionService->getSuggestions($invoice1);
        expect($suggestions1->isEmpty())->toBeTrue();

        $invoice2 = $this->createStudentWithInvoice();
        $suggestions2 = $this->suggestionService->getSuggestions($invoice2);
        expect($suggestions2->isEmpty())->toBeTrue();

        // 2. Create 3rd Invoice -> Should get suggestion
        $invoice3 = $this->createStudentWithInvoice();
        $suggestions3 = $this->suggestionService->getSuggestions($invoice3);

        expect($suggestions3->isNotEmpty())->toBeTrue();

        $suggestion = $suggestions3->first();
        expect($suggestion->policyCode)->toBe(DiscountPolicySuggestionService::POLICY_PAYER_THIRD_CHILD);
        expect($suggestion->suggestedAmount)->toEqual(1000.00); // 100% of Tuition
        expect($suggestion->eligibleItemIds)->toBeArray();
        expect($suggestion->eligibleItemIds)->toContain($invoice3->items->first()->id);
    }

    /** @test */
    public function it_does_not_suggest_if_students_are_less_than_three()
    {
        $invoice1 = $this->createStudentWithInvoice();
        $invoice2 = $this->createStudentWithInvoice();

        $suggestions = $this->suggestionService->getSuggestions($invoice2);
        expect($suggestions->isEmpty())->toBeTrue();
    }

    /** @test */
    public function it_does_not_apply_discount_automatically()
    {
        // Create 3rd invoice
        $this->createStudentWithInvoice();
        $this->createStudentWithInvoice();
        $invoice3 = $this->createStudentWithInvoice();

        // Check Suggestions exist
        $suggestions = $this->suggestionService->getSuggestions($invoice3);
        expect($suggestions->isNotEmpty())->toBeTrue();

        // BUT Check Database -> No discount applied
        $this->assertDatabaseCount('discount_applications', 0);

        $invoice3->refresh();
        expect($invoice3->total_amount)->toEqual(1000.00); // Full amount, no deduction
    }
}
