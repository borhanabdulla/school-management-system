<?php

namespace App\Livewire\HR;

use App\Domains\HR\Staff\Models\Staff;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;

/**
 * عرض تفاصيل الموظف
 */
class StaffShow extends Component
{
    #[Locked]
    public Staff $staff;

    public function mount(Staff $staff): void
    {
        $this->staff = $staff->load([
            'user',
            'teacher',
            'workShift',
            'leaveBalances.leaveType',
            'contracts' => fn($q) => $q->with('contractItems')->latest(),
            'payrollRecords.batch',
            'loans.installments',
            'staffAttendances' => fn($q) => $q->latest()->take(10)
        ]);
    }

    public function render()
    {
        return view('livewire.hr.staff-show')
            ->layout('layouts.app');
    }

    #[Computed]
    public function attendanceRate()
    {
        $monthKey = now()->format('Y-m');

        return \Illuminate\Support\Facades\Cache::remember(
            'staff_attendance_rate_' . $this->staff->id . '_' . $monthKey,
            now()->addHours(1),
            function () {
                $total = $this->staff->staffAttendances()->whereMonth('date', now()->month)->count();
                if ($total === 0)
                    return 0;

                $present = $this->staff->staffAttendances()
                    ->whereMonth('date', now()->month)
                    ->where('status', 'present')
                    ->count();

                return round(($present / $total) * 100);
            }
        );
    }

    #[Computed]
    public function leaveBalanceSummary()
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'staff_leave_summary_' . $this->staff->id,
            now()->addHours(1),
            function () {
                $total = $this->staff->leaveBalances()->sum('total_days');
                if ($total === 0)
                    return ['remaining' => 0, 'percentage' => 0];

                $remaining = $this->staff->leaveBalances()->sum('remaining_days');
                return [
                    'remaining' => $remaining,
                    'percentage' => round(($remaining / $total) * 100)
                ];
            }
        );
    }
}
