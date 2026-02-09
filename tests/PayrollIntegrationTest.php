<?php

namespace Tests;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Payroll\Models\SalaryComponent;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Services\PayrollCalculationService;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Load Laravel application
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Payroll Integration Test...\n";

DB::beginTransaction();

try {
    // 1. Create Salary Component
    echo "1. Creating Salary Component...\n";
    $component = SalaryComponent::create([
        'name' => 'Test Allowance',
        'type' => 'allowance',
        'fixed_value' => 500,
        'is_active' => true,
    ]);
    echo "   - Created: {$component->name} (500)\n";

    // 2. Create Staff
    echo "2. Creating Test Staff...\n";
    $staff = Staff::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);
    echo "   - Created: {$staff->full_name}\n";

    // 3. Create Contract
    echo "3. Creating Contract...\n";
    $contract = Contract::create([
        'staff_id' => $staff->id,
        'start_date' => Carbon::now()->startOfMonth(),
        'end_date' => Carbon::now()->addYear(),
        'basic_salary' => 5000,
        'status' => 'active',
    ]);

    $contract->contractItems()->create([
        'name' => $component->name,
        'amount' => $component->fixed_value,
        'type' => $component->type,
        'salary_component_id' => $component->id,
    ]);
    echo "   - Contract Created: Basic 5000 + Allowance 500\n";

    // 4. Calculate Payroll
    echo "4. Calculating Payroll...\n";
    $service = app(PayrollCalculationService::class);
    $batch = PayrollBatch::create([
        'name' => 'Test Batch',
        'year' => Carbon::now()->year,
        'month' => Carbon::now()->month,
        'period_start' => Carbon::now()->startOfMonth(),
        'period_end' => Carbon::now()->endOfMonth(),
        'status' => 'draft',
    ]);

    $result = $service->calculateForStaff($staff, $batch->period_start, $batch->period_end);

    // 5. Verify Results
    echo "5. Verifying Results...\n";
    $expectedGross = 5000 + 500;
    $actualGross = $result['totals']['gross_earnings'];

    echo "   - Expected Gross: {$expectedGross}\n";
    echo "   - Actual Gross: {$actualGross}\n";

    if (abs($expectedGross - $actualGross) < 0.01) {
        echo "✅ TEST PASSED: Calculations are correct.\n";
    } else {
        echo "❌ TEST FAILED: Mismatch in calculations.\n";
        throw new \Exception("Calculation mismatch");
    }

} catch (\Exception $e) {
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
} finally {
    DB::rollBack(); // Always rollback to keep DB clean
    echo "Test completed. Database rolled back.\n";
}
