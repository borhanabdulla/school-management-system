<?php

namespace App\Livewire\Payroll;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Config;

#[Layout('layouts.app')]
class PayrollPolicyEditor extends Component
{
    // Day Calculation
    public string $dayCalculationMethod = 'calendar_30';

    // Lateness Policy
    public array $latenessThresholds = [];

    // Absence Policy
    public float $absenceMultiplier = 1.0;

    // Pro-rata
    public bool $enableProrata = true;
    public string $prorataMethod = 'calendar';

    public function mount()
    {
        // Load from config
        $this->dayCalculationMethod = config('payroll.day_calculation_method', 'calendar_30');
        $this->latenessThresholds = config('payroll.lateness_thresholds', [
            ['min' => 0, 'max' => 15, 'deduction_minutes' => 0],
            ['min' => 15, 'max' => 30, 'deduction_minutes' => 30],
            ['min' => 30, 'max' => 60, 'deduction_minutes' => 60],
            ['min' => 60, 'max' => 999, 'deduction_minutes' => 'half_day'],
        ]);
        $this->absenceMultiplier = config('payroll.absence_multiplier', 1.0);
        $this->enableProrata = config('payroll.enable_prorata', true);
        $this->prorataMethod = config('payroll.prorata_method', 'calendar');
    }

    public function save()
    {
        // In a real app, you'd save to database or update .env
        // For now, we'll just show a success message
        $this->dispatch('notify', message: 'تم حفظ إعدادات السياسات بنجاح. (ملاحظة: لتفعيل الإعدادات بشكل دائم، عدّل config/payroll.php)', type: 'success');
    }

    public function addThreshold()
    {
        $this->latenessThresholds[] = [
            'min' => 0,
            'max' => 0,
            'deduction_minutes' => 0,
        ];
    }

    public function removeThreshold(int $index)
    {
        unset($this->latenessThresholds[$index]);
        $this->latenessThresholds = array_values($this->latenessThresholds);
    }

    public function render()
    {
        return view('livewire.payroll.payroll-policy-editor');
    }
}
