<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Guardian;
use App\Livewire\Student\StudentRegistration;
use App\Domains\Finance\Models\Invoice;
use App\Models\User;

class RegistrationFlowRegressionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_invoice_automatically_on_student_registration_scenario_A()
    {
        // 1. Prerequisites
        $user = User::factory()->create();
        $this->actingAs($user);

        // Active Year (Open)
        $year = AcademicYear::factory()->create([
            'name' => '2025-2026',
            'status' => 'active',
            'financial_status' => 'open'
        ]);

        // Fee Structure
        $grade = Grade::factory()->create(['name' => 'Grade 1']);
        $ft = FeeType::firstOrCreate(['name' => 'Tuition']);
        FeeStructure::create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'fee_type_id' => $ft->id,
            'amount' => 5000,
            'due_date' => now()->addMonth(),
        ]);

        // Guardian (For mocking the selection in UI, though UI usually creates or selects)
        // In this test we will simulate "New Guardian" input

        // Create Nationality (Country)
        $nationality = \Illuminate\Support\Facades\DB::table('countries')->insertGetId([
            'name_en' => 'Saudi', 
            'name_ar' => 'سعودي',
            'code' => 'SA',
        ]);

        // 2. Execute Livewire Flow
        Livewire::test(StudentRegistration::class)
            // Step 1: Basic Info
            ->set('form.first_name', 'Ali')
            ->set('form.first_name_ar', 'علي') // Requ
            ->set('form.family_name_ar', 'أحمد') // Re
            ->set('form.last_name', 'Ahmed')
            ->set('form.date_of_birth', now()->subYears(7)->format('Y-m-d'))
            ->set('form.gender', 'male')
            ->set('form.nationality_id', $nationality)
            ->set('form.grade_id', $grade->id)
            ->call('nextStep') // To 2

            // Step 2: Guardian
            ->set('isNewGuardian', true)
            ->set('form.guardian_first_name', 'Moneer')
            ->set('form.guardian_last_name', 'Saeed')
            ->set('form.guardian_national_id', '1000000001')
            ->set('form.guardian_phone', '0500000000')
            ->set('form.guardian_nationality_id', $nationality) // Often required too
            ->set('relationship', 'father')
            ->set('isFinancialSponsor', true) // CRITICAL
            ->call('addGuardianToList')
            ->call('nextStep') // To 3

            // Step 3: Address (Optional usually based on validation, let's fill basics)
            ->set('form.city', 'Riyadh')
            ->set('form.district', 'Olya')
            ->set('form.street_name', 'Main St')
            ->set('form.building_number', '12')
            ->call('nextStep') // To 4

            // Step 4: Documents (Skip or Mock)
            ->call('nextStep') // To 5 (Financials)

            // Debug state
            ->tap(function ($component) {
                if ($component->get('currentStep') !== 5) {
                    dump('Stuck on step: ' . $component->get('currentStep'));
                    dump($component->errors());
                }
            })

            // Step 5: Financials
            // The component loads financials on step 5 entry
            // create_invoice is true by default
            ->assertSet('create_invoice', true)
            // Verify total calculation
            ->assertSet('final_total', 5000)

            // Submit
            ->call('submit')
            ->tap(function ($component) {
                 if ($component->errors()->isNotEmpty()) {
                     dump('Submit Errors:');
                     dump($component->errors());
                 }
            });

        // 3. Verification (The Definition of Done)

        // Find Student
        // Find Student
        $student = \App\Domains\Academic\Student\Models\Student::where('first_name_ar', 'علي')->first();
        $this->assertNotNull($student, 'Student should be created');

        // (1) Invoice Created
        $invoice = Invoice::where('student_id', $student->id)->first();
        $this->assertNotNull($invoice, 'Invoice should be created');

        $this->assertEquals($year->id, $invoice->academic_year_id, 'Invoice Year Mismatch');
        $this->assertEquals('unpaid', $invoice->status->value, 'Invoice Status Mismatch');

        // (2) Payer Snapshot
        $guardian = Guardian::where('national_id', '1000000001')->first();
        $this->assertEquals($guardian->id, $invoice->payer_guardian_id, 'Payer Snapshot Mismatch');
        $this->assertEquals(0, $invoice->paid_amount, 'Paid Amount should be 0');
        $this->assertEquals(5000, $invoice->total_amount, 'Total Amount should equal Fee Structure');

        // (3) Invoice Items
        $this->assertTrue($invoice->items()->exists(), 'Invoice Items should exist');
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals(5000, $invoice->items()->first()->amount);

        // (4) No Discounts
        $this->assertEquals(0, \App\Domains\Finance\Models\DiscountApplication::count(), 'No discounts should be applied automatically');

        // (5) No Exceptions (Implicit by test passing)
    }
}
