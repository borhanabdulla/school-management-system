<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\HR\Payroll\Models\SalaryComponent;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\Loan;
use App\Domains\Shared\Models\User;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⏳ Seeding Payroll Data...');

        DB::transaction(function () {
            // 1. Seed Salary Components
            $this->seedSalaryComponents();

            // 2. Seed Contracts for Staff
            $this->seedContracts();

            // 3. Seed Loans/Advances
            $this->seedLoans();

            // 4. Seed Payroll Batches (Previous Month)
            $this->seedPayroll();
        });

        $this->command->info('✅ Payroll Data Seeded Successfully!');
    }

    private function seedSalaryComponents(): void
    {
        $components = [
            [
                'name' => 'Housing Allowance',
                'type' => 'allowance',
                'is_percentage' => true,
                'percentage_value' => 25.00,
                'is_active' => true,
            ],
            [
                'name' => 'Transport Allowance',
                'type' => 'allowance',
                'is_percentage' => false,
                'fixed_value' => 500.00, // Fixed amount
                'is_active' => true,
            ],
            [
                'name' => 'GOSI (Social Security)',
                'type' => 'deduction',
                'is_percentage' => true,
                'percentage_value' => 9.00,
                'is_active' => true,
            ],
        ];

        foreach ($components as $data) {
            SalaryComponent::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
        $this->command->info('   - Salary Components Created.');
    }

    private function seedContracts(): void
    {
        $admin = Staff::whereHas('user', fn($q) => $q->where('email', 'admin@school.com'))->first();
        $teacher = Staff::whereHas('user', fn($q) => $q->where('email', 'teacher01@school.com'))->first();
        $activeYear = AcademicYear::where('status', 'active')->first();

        if (!$activeYear) {
            $this->command->warn('   ! No active academic year found. Skipping contracts.');
            return;
        }

        $components = SalaryComponent::all();

        // Seed Admin Contract
        if ($admin && !$admin->contracts()->exists()) {
            $contract = Contract::create([
                'staff_id' => $admin->id,
                'start_date' => $activeYear->start_date,
                'end_date' => $activeYear->end_date,
                'basic_salary' => 15000.00,
                'bank_name' => 'Al Rajhi Bank',
                'iban' => 'SA0000000000000000000001',
                'status' => \App\Domains\HR\Payroll\Enums\ContractStatus::Active,
                'academic_year_id' => $activeYear->id,
            ]);

            // Add Items (Housing + Transport - GOSI)
            foreach ($components as $comp) {
                // Calculate amount based on basic salary
                $amount = $comp->is_percentage
                    ? (15000.00 * ($comp->percentage_value / 100))
                    : $comp->fixed_value;

                $contract->contractItems()->create([
                    'name' => $comp->name,
                    'type' => $comp->type,
                    'amount' => $amount,
                    'is_one_time' => false,
                ]);
            }
        }

        // Seed Teacher Contract
        if ($teacher && !$teacher->contracts()->exists()) {
            $contract = Contract::create([
                'staff_id' => $teacher->id,
                'start_date' => $activeYear->start_date,
                'end_date' => $activeYear->end_date,
                'basic_salary' => 8000.00,
                'bank_name' => 'SNB',
                'iban' => 'SA0000000000000000000002',
                'status' => \App\Domains\HR\Payroll\Enums\ContractStatus::Active,
                'academic_year_id' => $activeYear->id,
            ]);

            foreach ($components as $comp) {
                $amount = $comp->is_percentage
                    ? (8000.00 * ($comp->percentage_value / 100))
                    : $comp->fixed_value;

                $contract->contractItems()->create([
                    'name' => $comp->name,
                    'type' => $comp->type,
                    'amount' => $amount,
                    'is_one_time' => false,
                ]);
            }
        }

        $this->command->info('   - Active Contracts Created.');
    }

    private function seedLoans(): void
    {
        $teacher = Staff::whereHas('user', fn($q) => $q->where('email', 'teacher01@school.com'))->first();

        if ($teacher && !$teacher->loans()->exists()) {
            $amount = 2000.00;
            $installments = 2;

            $loan = Loan::create([
                'staff_id' => $teacher->id,
                'amount' => $amount,
                'paid_amount' => 0,
                'installments_count' => $installments,
                'monthly_installment' => $amount / $installments,
                'reason' => 'Emergency medical expenses',
                'status' => \App\Domains\HR\Payroll\Enums\LoanStatus::Approved,
                'start_date' => now()->startOfMonth(), // Deduction starts this month
                'approved_at' => now()->subMonths(1),
                'approved_by' => User::first()->id ?? 1,
                'created_at' => now()->subMonths(1),
            ]);

            // Create Installments
            for ($i = 0; $i < $installments; $i++) {
                $dueDate = now()->startOfMonth()->addMonths($i);
                $loan->installments()->create([
                    'amount' => $amount / $installments,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                ]);
            }
        }
        $this->command->info('   - Loans (Advances) Simulated.');
    }

    private function seedPayroll(): void
    {
        // Generate for LAST MONTH to avoid conflict with current month if verified
        $lastMonth = now()->subMonth();

        // Use Action!
        $action = app(GeneratePayrollAction::class);
        $adminUserId = User::where('email', 'admin@school.com')->value('id') ?? 1;

        $dto = new PayrollGenerationData(
            period_start: $lastMonth->copy()->startOfMonth(),
            period_end: $lastMonth->copy()->endOfMonth(),
            year: $lastMonth->year,
            month: $lastMonth->month,
            notes: 'Seeded Payroll for ' . $lastMonth->format('F Y')
        );

        try {
            $batch = $action->execute($dto, $adminUserId);
            // Optionally Approve it?
            // $approveAction = app(ApprovePayrollAction::class); // If exists
            $batch->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $adminUserId]);

            $this->command->info("   - Payroll Batch Generated for {$lastMonth->format('F Y')}.");
        } catch (\Exception $e) {
            $this->command->warn("   ! Payroll Generation Skipped: " . $e->getMessage());
        }
    }
}
