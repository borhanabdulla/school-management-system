<?php

namespace App\Livewire\Payroll;

use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Leave\Models\LeaveRequest;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class PayrollProcessor extends Component
{
    #[Locked]
    public $year;

    #[Locked]
    public $month;

    public $name;
    public $notes;

    public $step = 1; // 1: Config, 2: Preview/Validation, 3: Processing, 4: Done
    public $validationErrors = [];
    public $stats = [];

    public function mount()
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->updateName();
    }

    public function updatedYear()
    {
        $this->updateName();
    }
    public function updatedMonth()
    {
        $this->updateName();
    }

    protected function updateName()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1);
        $this->name = "مسير رواتب " . $date->translatedFormat('F Y');
    }

    public function validateConfig()
    {
        $this->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'name' => 'required|string|max:255',
        ]);

        // Check if batch exists
        if (PayrollBatch::where('year', $this->year)->where('month', $this->month)->exists()) {
            $this->addError('month', 'تم إنشاء مسير لهذه الفترة مسبقاً.');
            return;
        }

        // Run Fail-Safe Validation
        $this->runFailSafeValidation();

        $this->step = 2;
    }

    protected function runFailSafeValidation()
    {
        $this->validationErrors = [];
        $start = Carbon::createFromDate($this->year, $this->month, 1);
        $end = $start->copy()->endOfMonth();

        // 1. Check for active contracts
        $activeContractsCount = Contract::active()->forPeriod($start)->count();
        if ($activeContractsCount === 0) {
            $this->validationErrors[] = [
                'type' => 'error',
                'message' => 'لا توجد عقود نشطة في هذه الفترة. لا يمكن إنشاء المسير.',
            ];
        } else {
            $this->stats['contracts_count'] = $activeContractsCount;
        }

        // 2. Check for employees without contracts (Optional warning)
        // ...

        // 3. Check for pending substitutions (that should be paid)
        // ...

        // 3. Smart Alerts
        $this->checkSmartAlerts($start);
    }

    protected function checkSmartAlerts(Carbon $start)
    {
        // Alert 1: Staff with 0 Basic Salary
        $zeroSalaryCount = Contract::active()
            ->forPeriod($start)
            ->where('basic_salary', 0)
            ->count();

        if ($zeroSalaryCount > 0) {
            $this->validationErrors[] = [
                'type' => 'warning',
                'message' => "يوجد {$zeroSalaryCount} موظف براتب أساسي 0. يرجى التحقق من العقود.",
            ];
        }

        // Alert 2: Pending Leave Requests
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->where(function ($q) use ($start) {
                $q->whereMonth('start_date', $start->month)
                    ->orWhereMonth('end_date', $start->month);
            })->count();

        if ($pendingLeaves > 0) {
            $this->validationErrors[] = [
                'type' => 'warning',
                'message' => "يوجد {$pendingLeaves} طلب إجازة معلق قد يؤثر على الرواتب.",
            ];
        }
    }

    public function generate(GeneratePayrollAction $action)
    {
        $this->step = 3;

        try {
            $start = Carbon::createFromDate($this->year, $this->month, 1);
            $end = $start->copy()->endOfMonth();

            $data = new PayrollGenerationData(
                period_start: $start,
                period_end: $end,
                year: (int) $this->year,
                month: (int) $this->month,
                name: $this->name,
                notes: $this->notes
            );

            $batch = $action->execute($data, auth()->id());

            $this->step = 4;
            $this->dispatch('notify', message: 'تم إنشاء مسير الرواتب بنجاح!', type: 'success');

            return redirect()->route('payroll.batches'); // Or stay and show summary

        } catch (\Exception $e) {
            $this->step = 2;
            $this->addError('generation', 'حدث خطأ أثناء التوليد: ' . $e->getMessage());
        }
    }

    public function back()
    {
        $this->step = max(1, $this->step - 1);
    }

    public function render()
    {
        return view('livewire.payroll.payroll-processor');
    }
}
