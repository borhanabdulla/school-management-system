<?php

namespace Tests\Feature;

use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Domains\HR\Payroll\Models\Loan;
use App\Domains\HR\Payroll\Models\LoanInstallment;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Staff\Models\Staff;
use App\Models\User;
use App\Domains\HR\Payroll\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_split_month_calculation()
    {
        // 1. Setup Staff
        $staff = Staff::factory()->create();
        $user = User::factory()->create();

        // Create Academic Year manually if factory doesn't exist or to be safe
        \App\Domains\Academic\AcademicYear\Models\AcademicYear::create([
            'id' => 1,
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'status' => 'active'
        ]);

        // 2. Setup Contracts (Split Month)
        // Contract A: 1st to 15th (Basic: 3000)
        Contract::create([
            'staff_id' => $staff->id,
            'start_date' => Carbon::create(2024, 1, 1),
            'end_date' => Carbon::create(2024, 1, 15),
            'basic_salary' => 3000,
            'status' => 'active',
            'academic_year_id' => 1,
        ]);

        // Contract B: 16th to End (Basic: 6000)
        Contract::create([
            'staff_id' => $staff->id,
            'start_date' => Carbon::create(2024, 1, 16),
            'end_date' => Carbon::create(2024, 12, 31),
            'basic_salary' => 6000,
            'status' => 'active',
            'academic_year_id' => 1,
        ]);

        // 3. Run Calculation
        $service = app(PayrollCalculationService::class);
        $periodStart = Carbon::create(2024, 1, 1);
        $periodEnd = Carbon::create(2024, 1, 31);

        $result = $service->calculateForStaff($staff, $periodStart, $periodEnd);

        // 4. Verify
        // Days: 15 days @ 3000 + 15 days @ 6000 (Assuming 30 day month policy)
        // Rate A: 3000 / 30 = 100/day * 15 = 1500
        // Rate B: 6000 / 30 = 200/day * 16 = 3200
        // Total Basic: 1500 + 3200 = 4700

        $basicEarnings = collect($result['earnings'])->where('category', 'basic')->sum('amount');

        $this->assertEquals(4700, $basicEarnings, 'Split month basic salary calculation is incorrect.');
        $this->assertEquals(30, $result['totals']['working_days']);
    }

    public function test_loan_deduction()
    {
        // 1. Setup Staff & Contract
        $staff = Staff::factory()->create();

        // Ensure Academic Year exists (if not created in previous test - tests are isolated)
        if (\App\Domains\Academic\AcademicYear\Models\AcademicYear::count() === 0) {
            \App\Domains\Academic\AcademicYear\Models\AcademicYear::create([
                'id' => 1,
                'name' => '2023-2024',
                'start_date' => '2023-09-01',
                'end_date' => '2024-06-30',
                'status' => 'active'
            ]);
        }

        Contract::create([
            'staff_id' => $staff->id,
            'start_date' => Carbon::create(2024, 1, 1),
            'end_date' => Carbon::create(2024, 12, 31),
            'basic_salary' => 5000,
            'status' => 'active',
            'academic_year_id' => 1,
        ]);

        // 2. Setup Loan
        $loan = Loan::create([
            'staff_id' => $staff->id,
            'amount' => 1000,
            'status' => 'approved',
            'request_date' => now(),
            'installments_count' => 2,
            'monthly_installment' => 500,
            'start_date' => now(),
        ]);

        LoanInstallment::create([
            'loan_id' => $loan->id,
            'amount' => 500,
            'due_date' => Carbon::create(2024, 2, 1), // Due in Feb
            'status' => 'pending',
        ]);

        // 3. Run Calculation for Feb
        $service = app(PayrollCalculationService::class);
        $periodStart = Carbon::create(2024, 2, 1);
        $periodEnd = Carbon::create(2024, 2, 29); // Leap year? 2024 is leap. But policy might be 30 fixed.

        $result = $service->calculateForStaff($staff, $periodStart, $periodEnd);

        // 4. Verify Deduction
        $loanDeductions = collect($result['deductions'])->where('category', 'loan')->sum('amount');
        $this->assertEquals(500, $loanDeductions, 'Loan deduction not found or incorrect.');
    }
}
