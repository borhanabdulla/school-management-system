<?php

namespace App\Livewire\Finance;

use App\Domains\Finance\Services\OwnerDashboardService;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Finance\CashFlowExport;
use App\Exports\Finance\ReceivablesExport;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;

/**
 * لوحة تحكم المالك - عرض الكاش والمستحقات
 */
class OwnerDashboard extends Component
{
    public string $period = 'month'; // today, month, year
    public ?int $selectedYearId = null;
    public array $years = [];

    public array $dashboardData = [];

    protected OwnerDashboardService $dashboardService;

    public function boot(OwnerDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function mount()
    {
        $this->years = AcademicYear::orderBy('start_date', 'desc')->get()->toArray();
        $this->selectedYearId = school()->activeYearId(); // UI Default Only
        $this->loadDashboardData();
    }

    public function updatedPeriod()
    {
        $this->loadDashboardData();
    }

    public function updatedSelectedYearId()
    {
        $this->loadDashboardData();
    }

    protected function loadDashboardData()
    {
        $this->dashboardData = $this->dashboardService->getDashboardData($this->period, $this->selectedYearId);
    }

    public function render()
    {
        return view('livewire.finance.owner-dashboard');
    }

    public function exportCashFlow()
    {
        return Excel::download(new CashFlowExport, 'cash-flow-' . date('Y-m-d') . '.xlsx');
    }

    public function exportReceivables()
    {
        return Excel::download(new ReceivablesExport, 'receivables-' . date('Y-m-d') . '.xlsx');
    }
}
