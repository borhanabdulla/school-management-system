<?php

namespace App\Livewire\HR\Leave;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Leave\Services\LeaveService;
use App\Domains\HR\Leave\Services\LeaveLookupService;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaveRequestForm extends Component
{
    use WithFileUploads;

    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $reason;
    public $attachment;

    public $days_count = 0;

    protected function rules()
    {
        return [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:2048', // 2MB max
        ];
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['start_date', 'end_date', 'leave_type_id'])) {
            $this->calculateDays();
        }
    }

    public function calculateDays()
    {
        if ($this->start_date && $this->end_date && $this->leave_type_id) {
            try {
                $service = app(LeaveService::class);
                $calculation = $service->calculateActualDays($this->start_date, $this->end_date, $this->leave_type_id);
                $this->days_count = $calculation['days'];

                // Optional: You could add a property to show the difference
                // $this->total_days = Carbon::parse($this->start_date)->diffInDays($this->end_date) + 1;
            } catch (\Exception $e) {
                $this->days_count = 0;
            }
        } else {
            $this->days_count = 0;
        }
    }

    public function submit(LeaveService $service)
    {
        $this->validate();

        // Assuming the logged-in user is linked to a staff member
        // For now, we'll fetch the staff record for the current user.
        // If the user is an admin testing, we might need a way to select staff or assume a test staff.
        // Let's assume auth()->user()->staff exists.

        $staff = Staff::where('user_id', auth()->id())->first();

        if (!$staff) {
            $this->dispatch('error', message: 'عفواً، لا يوجد ملف موظف مرتبط بحسابك.');
            return;
        }

        try {
            $data = [
                'staff_id' => $staff->id,
                'leave_type_id' => $this->leave_type_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'reason' => $this->reason,
                'attachment' => $this->attachment ? $this->attachment->store('leave_attachments', 'public') : null,
            ];

            $service->submitRequest($data);

            $this->dispatch('notify', message: 'تم تقديم طلب الإجازة بنجاح.', type: 'success');
            $this->reset();
            $this->days_count = 0;

        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $lookupService = app(LeaveLookupService::class);

        return view('livewire.hr.leave.leave-request-form', [
            'leaveTypes' => $lookupService->getLeaveTypes(),
        ])->layout('layouts.app');
    }
}
