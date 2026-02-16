<?php

namespace App\Livewire\Payroll;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Enums\PayrollBatchStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class PayrollDashboard extends Component
{
    public $stats = [];
    public $monthlyTrend = [];
    public $costDistribution = [];
    public $recentBatches = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadChartsData();
        $this->loadRecentActivity();
    }

    public function loadStats()
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // 1. Total Payroll Cost (Current Month)
        // We look for a batch in the current month. If not found, we estimate from active contracts.
        // For accuracy, let's use the APPROVED batch if exists, otherwise estimate.
        // Actually, for a dashboard, "Current Month Cost" usually means "What did we pay or will we pay?".
        // Let's sum up active contracts' basic + allowances for a quick estimate if no batch exists.

        $currentBatch = PayrollBatch::where('year', $currentMonth->year)
            ->where('month', $currentMonth->month)
            ->first();

        $currentCost = $currentBatch ? $currentBatch->total_net : $this->estimateCurrentPayroll();

        // Last Month Cost for Trend
        $lastBatch = PayrollBatch::where('year', $lastMonth->year)
            ->where('month', $lastMonth->month)
            ->first();
        $lastCost = $lastBatch ? $lastBatch->total_net : 0;

        $trend = 0;
        if ($lastCost > 0) {
            $trend = (($currentCost - $lastCost) / $lastCost) * 100;
        }

        // 2. Active Staff
        $activeStaff = Staff::whereHas('contracts', function ($q) {
            $q->active();
        })->count();

        // 3. Pending Batches
        $pendingBatches = PayrollBatch::whereIn('status', [PayrollBatchStatus::Draft, PayrollBatchStatus::Frozen])->count();

        $this->stats = [
            'total_cost' => $currentCost,
            'cost_trend' => round($trend, 1),
            'active_staff' => $activeStaff,
            'pending_batches' => $pendingBatches,
        ];
    }

    protected function estimateCurrentPayroll()
    {
        // Simple estimation: Sum of active contracts' basic salary + fixed allowances
        // This is a rough estimate for the dashboard when no batch exists.
        return Contract::active()->sum('basic_salary');
        // Note: This ignores allowances for simplicity in this estimation, 
        // or we could load them. For now, basic salary is a safe lower bound.
    }

    public function loadChartsData()
    {
        // 1. Monthly Trend (Last 6 Months)
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $batch = PayrollBatch::where('year', $date->year)
                ->where('month', $date->month)
                ->first();

            $trendData[] = [
                'month' => $date->translatedFormat('M'),
                'gross' => $batch ? $batch->total_gross : 0,
                'net' => $batch ? $batch->total_net : 0,
            ];
        }
        $this->monthlyTrend = $trendData;

        // PR-CF3: Cost Distribution using Service Layer
        // ✅ No more DB::table in Livewire!
        $this->costDistribution = app(\App\Domains\HR\Payroll\Services\PayrollReportService::class)
            ->getCostDistribution();
    }

    public function loadRecentActivity()
    {
        $this->recentBatches = PayrollBatch::with('generatedByUser')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.payroll.payroll-dashboard');
    }
}
