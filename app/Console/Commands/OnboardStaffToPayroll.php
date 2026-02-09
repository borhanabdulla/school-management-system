<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Console\Command;

class OnboardStaffToPayroll extends Command
{
    protected $signature = 'payroll:onboard-staff';
    protected $description = 'Create default contracts for staff members who do not have one';

    public function handle()
    {
        $this->info('Starting payroll onboarding for existing staff...');

        $staffWithoutContracts = Staff::whereDoesntHave('contracts')->get();

        if ($staffWithoutContracts->isEmpty()) {
            $this->info('All staff members already have contracts.');
            return;
        }

        $this->info("Found {$staffWithoutContracts->count()} staff members without contracts.");

        $currentYear = school()->activeYear();
        $startDate = now()->startOfMonth();
        $endDate = now()->addYear()->endOfMonth();

        foreach ($staffWithoutContracts as $staff) {
            $this->info("Creating contract for: {$staff->full_name}");

            $contract = Contract::create([
                'staff_id' => $staff->id,
                'academic_year_id' => $currentYear?->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'basic_salary' => $staff->basic_salary > 0 ? $staff->basic_salary : 5000, // Fallback or use staff's basic salary
                'status' => 'active',
                'is_locked' => false,
            ]);

            // Add default allowances (Example)
            $contract->contractItems()->createMany([
                [
                    'name' => 'بدل نقل',
                    'amount' => 500,
                    'type' => 'allowance',
                ],
                [
                    'name' => 'تأمينات اجتماعية',
                    'amount' => $contract->basic_salary * 0.09, // 9%
                    'type' => 'deduction',
                ],
            ]);
        }

        $this->info('Onboarding completed successfully.');
    }
}
