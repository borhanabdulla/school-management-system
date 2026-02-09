<?php

namespace App\Livewire\HR\Leave;

use Livewire\Attributes\Layout;
use Livewire\Component;

use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Staff\Models\Staff;

use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EmployeeLeaveDashboard extends Component
{
    use WithPagination;

    public $staff;
    public $balances = [];
    public $year;

    public function mount()
    {
        $user = Auth::user();
        $this->staff = $user->staff;
        $this->year = (int) (school()->activeYear()?->start_date?->format('Y') ?? now()->year);

        if (!$this->staff) {
            abort(403, 'User is not linked to a staff record.');
        }


        $this->loadBalances();
    }

    public function loadBalances()
    {
        $this->balances = [];
        $leaveTypes = LeaveType::where('is_active', true)->get();

        foreach ($leaveTypes as $type) {
            $balance = $this->staff->leaveBalances()
                ->where('leave_type_id', $type->id)
                ->where('year', $this->year)
                ->first();

            $total = $type->days_per_year;
            $remaining = $balance ? $balance->remaining_days : $total; // Default to total if no record (assuming fresh start)

            // Correction: If no balance record exists, it effectively means full balance is available 
            // (handled by Service auto-fix, but for display we show full)

            $used = $total - $remaining;
            $percentage = $total > 0 ? ($used / $total) * 100 : 0;

            $this->balances[] = [
                'type' => $type->name,
                'total' => $total,
                'remaining' => $remaining,
                'used' => $used,
                'percentage' => round($percentage),
                'color' => $this->getColorForPercentage($percentage),
            ];
        }
    }

    private function getColorForPercentage($percentage)
    {
        if ($percentage >= 90)
            return 'red';
        if ($percentage >= 75)
            return 'orange';
        if ($percentage >= 50)
            return 'yellow';
        return 'green';
    }

    public function render()
    {
        $requests = LeaveRequest::where('staff_id', $this->staff->id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.hr.leave.employee-leave-dashboard', [
            'requests' => $requests
        ]);
    }
}
