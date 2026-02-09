<?php

namespace App\Livewire\HR\Leave;

use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Leave\Services\LeaveService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class LeaveApprovalManager extends Component
{
    use WithPagination;

    public $statusFilter = '';
    #[Locked]
    public $rejectingId = null;
    public $rejectionReason = '';

    protected LeaveService $leaveService;

    public function boot(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function approve($id)
    {
        try {
            $request = LeaveRequest::findOrFail($id);
            $this->leaveService->approveRequest($request, auth()->id());
            $this->dispatch('notify', message: 'تم قبول طلب الإجازة بنجاح.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function confirmReject($id)
    {
        $this->rejectingId = $id;
        $this->rejectionReason = '';
    }

    public function cancelReject()
    {
        $this->rejectingId = null;
        $this->rejectionReason = '';
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5',
        ], [
            'rejectionReason.required' => 'يرجى كتابة سبب الرفض.',
            'rejectionReason.min' => 'سبب الرفض يجب أن يكون 5 أحرف على الأقل.',
        ]);

        try {
            $request = LeaveRequest::findOrFail($this->rejectingId);
            $this->leaveService->rejectRequest($request, auth()->id(), $this->rejectionReason);

            $this->dispatch('notify', message: 'تم رفض طلب الإجازة.', type: 'success');
            $this->cancelReject();
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $requests = LeaveRequest::with(['staff', 'leaveType'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.hr.leave.leave-approval-manager', [
            'requests' => $requests,
        ]);
    }
}
