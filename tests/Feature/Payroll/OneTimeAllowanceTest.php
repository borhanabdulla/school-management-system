<?php

namespace Tests\Feature\Payroll;

use Tests\TestCase;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class OneTimeAllowanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedActiveAcademicYearForDate(now());
    }

    public function test_one_time_allowance_is_paid_only_once(): void
    {
        $staff = Staff::factory()->create();

        $contract = Contract::create([
            'staff_id' => $staff->id,
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addYear(),
            'basic_salary' => 5000,
            'status' => 'active',
        ]);

        // بدل لمرة واحدة
        $oneTimeBonus = ContractItem::create([
            'contract_id' => $contract->id,
            'type' => 'allowance',
            'name' => 'مكافأة تعيين',
            'amount' => 1000,
            'is_one_time' => true,
            'consumed_at' => null,
        ]);

        // الشهر الأول
        $batch1 = app(GeneratePayrollAction::class)->execute(new PayrollGenerationData(
            period_start: now()->startOfMonth(),
            period_end: now()->endOfMonth(),
            year: now()->year,
            month: now()->month,
            name: 'Batch 1'
        ), 1);

        $record1 = $batch1->records()->where('staff_id', $staff->id)->first();
        $this->assertTrue($record1->items()->where('name', 'مكافأة تعيين')->exists(), 'One-time bonus should be paid in first month');

        $batch1->update(['status' => \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Frozen]);
        app(\App\Domains\HR\Payroll\Actions\ApprovePayrollAction::class)->execute($batch1, 1);

        $oneTimeBonus->refresh();
        $this->assertNotNull($oneTimeBonus->consumed_at, 'Bonus should be marked consumed');

        // الشهر الثاني
        $nextMonth = now()->addMonth();
        $batch2 = app(GeneratePayrollAction::class)->execute(new PayrollGenerationData(
            period_start: $nextMonth->startOfMonth(),
            period_end: $nextMonth->endOfMonth(),
            year: $nextMonth->year,
            month: $nextMonth->month,
            name: 'Batch 2'
        ), 1);

        $record2 = $batch2->records()->where('staff_id', $staff->id)->first();
        $this->assertFalse($record2->items()->where('name', 'مكافأة تعيين')->exists(), 'One-time bonus should NOT be paid in second month');
    }

    public function test_recurring_allowances_are_paid_every_month(): void
    {
        $staff = Staff::factory()->create();

        $contract = Contract::create([
            'staff_id' => $staff->id,
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addYear(),
            'basic_salary' => 5000,
            'status' => 'active',
        ]);

        // بدل متكرر
        ContractItem::create([
            'contract_id' => $contract->id,
            'type' => 'allowance',
            'name' => 'بدل نقل',
            'amount' => 500,
            'is_one_time' => false,
        ]);

        // الشهر الأول
        $batch1 = app(GeneratePayrollAction::class)->execute(new PayrollGenerationData(
            period_start: now()->startOfMonth(),
            period_end: now()->endOfMonth(),
            year: now()->year,
            month: now()->month,
            name: 'Batch 1'
        ), 1);

        // الشهر الثاني
        $nextMonth = now()->addMonth();
        $batch2 = app(GeneratePayrollAction::class)->execute(new PayrollGenerationData(
            period_start: $nextMonth->startOfMonth(),
            period_end: $nextMonth->endOfMonth(),
            year: $nextMonth->year,
            month: $nextMonth->month,
            name: 'Batch 2'
        ), 1);

        $record1 = $batch1->records()->where('staff_id', $staff->id)->first();
        $record2 = $batch2->records()->where('staff_id', $staff->id)->first();

        $this->assertTrue($record1->items()->where('name', 'بدل نقل')->exists());
        $this->assertTrue($record2->items()->where('name', 'بدل نقل')->exists());
    }
}
